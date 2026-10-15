<?php

namespace App\Support\Updates;

use App\Enums\UpdateStatus;
use App\Models\SystemUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;
use ZipArchive;

/**
 * Installs release packages uploaded in the admin (for hosts without SSH).
 *
 * A package is a .zip whose root mirrors the project (app/, config/, routes/...)
 * plus a softia-update.json manifest. Protected paths such as .env and storage/
 * are never touched. Every replaced file is backed up first so an update can be
 * rolled back, and a failed install restores the backup automatically.
 */
class UpdateInstaller
{
    public const MANIFEST = 'softia-update.json';

    /**
     * Never written by an update: secrets, uploads, logs, caches, tooling.
     */
    public const PROTECTED_PREFIXES = [
        '.env',
        'storage/',
        'public/storage/',
        'bootstrap/cache/',
        '.git/',
        'node_modules/',
        '.claude/',
    ];

    private const MAX_FILES = 25000;

    public function __construct(private ?string $basePath = null)
    {
        $this->basePath ??= base_path();
    }

    /**
     * Validate a package and register it as pending.
     */
    public function stage(string $zipPath, ?User $user = null): SystemUpdate
    {
        $manifest = $this->inspect($zipPath);

        return SystemUpdate::create([
            'version' => $manifest['version'],
            'notes' => $manifest['notes'],
            'package_path' => $zipPath,
            'status' => UpdateStatus::Pending,
            'files' => $manifest['files'],
            'log' => $manifest['skipped'] === [] ? null : "Skipped protected paths:\n".implode("\n", $manifest['skipped']),
            'user_id' => $user?->id,
        ]);
    }

    /**
     * @return array{version: string, notes: ?string, migrations: bool, assets: bool, files: list<string>, entries: array<string, string>, skipped: list<string>}
     */
    public function inspect(string $zipPath): array
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new InvalidUpdatePackage('The file is not a valid .zip archive.');
        }

        try {
            $manifestJson = $zip->getFromName(self::MANIFEST);

            if ($manifestJson === false) {
                throw new InvalidUpdatePackage('Missing '.self::MANIFEST.' at the root of the package. Build packages with: php artisan softia:make-update');
            }

            $manifest = json_decode($manifestJson, true);

            if (! is_array($manifest) || blank($manifest['version'] ?? null)) {
                throw new InvalidUpdatePackage(self::MANIFEST.' must contain a "version".');
            }

            if ($zip->numFiles > self::MAX_FILES) {
                throw new InvalidUpdatePackage('The package contains too many files.');
            }

            $files = [];
            $entries = [];
            $skipped = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);

                if (str_ends_with($name, '/') || $name === self::MANIFEST) {
                    continue;
                }

                $path = $this->safeRelativePath($name);

                if ($this->isProtected($path)) {
                    $skipped[] = $path;

                    continue;
                }

                $files[] = $path;
                $entries[$path] = $name;
            }

            if ($files === []) {
                throw new InvalidUpdatePackage('The package does not contain any files to install.');
            }

            return [
                'version' => (string) $manifest['version'],
                'notes' => isset($manifest['notes']) ? (string) $manifest['notes'] : null,
                'migrations' => (bool) ($manifest['migrations'] ?? true),
                'assets' => (bool) ($manifest['filament_assets'] ?? false),
                'files' => $files,
                'entries' => $entries,
                'skipped' => $skipped,
            ];
        } finally {
            $zip->close();
        }
    }

    public function apply(SystemUpdate $update): SystemUpdate
    {
        if ($update->status !== UpdateStatus::Pending) {
            throw new InvalidUpdatePackage('Only a pending update can be installed.');
        }

        $manifest = $this->inspect($update->package_path);
        $backupPath = storage_path('app/private/update-backups/'.$update->id.'-'.now()->format('YmdHis'));
        $log = [];
        $written = [];
        $added = [];

        $zip = new ZipArchive;
        $zip->open($update->package_path);

        try {
            foreach ($manifest['files'] as $file) {
                $target = $this->basePath.DIRECTORY_SEPARATOR.$file;

                if (is_file($target)) {
                    File::ensureDirectoryExists(dirname($backupPath.'/'.$file));
                    File::copy($target, $backupPath.'/'.$file);
                } else {
                    $added[] = $file;
                }

                $contents = $zip->getFromName($manifest['entries'][$file]);

                if ($contents === false) {
                    throw new InvalidUpdatePackage("Could not read {$file} from the package.");
                }

                File::ensureDirectoryExists(dirname($target));
                File::put($target, $contents);
                $written[] = $file;
            }

            $log[] = 'Installed '.count($written).' files ('.count($added).' new).';
        } catch (Throwable $e) {
            $this->restore($written, $added, $backupPath);
            $zip->close();

            $update->update([
                'status' => UpdateStatus::Failed,
                'log' => trim(($update->log ? $update->log."\n\n" : '').'Install failed and was reverted: '.$e->getMessage()),
            ]);

            return $update;
        }

        $zip->close();

        $log = array_merge($log, $this->afterFilesChanged($manifest['migrations'], $manifest['assets']));

        $update->update([
            'status' => UpdateStatus::Applied,
            'added_files' => $added,
            'backup_path' => $backupPath,
            'applied_at' => now(),
            'log' => trim(($update->log ? $update->log."\n\n" : '').implode("\n\n", $log)),
        ]);

        return $update;
    }

    /**
     * Restore replaced files and remove files the update added.
     * Database migrations are not reversed.
     */
    public function rollback(SystemUpdate $update): SystemUpdate
    {
        if (! $update->canRollBack()) {
            throw new InvalidUpdatePackage('Only the most recently installed update can be rolled back.');
        }

        $this->restore($update->files ?? [], $update->added_files ?? [], $update->backup_path);
        $log = $this->afterFilesChanged(migrate: false, assets: false);

        $update->update([
            'status' => UpdateStatus::RolledBack,
            'log' => trim($update->log."\n\nRolled back on ".now()->format('j M Y H:i').".\n".implode("\n", $log)),
        ]);

        return $update;
    }

    public function discard(SystemUpdate $update): void
    {
        if ($update->status === UpdateStatus::Pending) {
            File::delete($update->package_path);
            $update->delete();
        }
    }

    /**
     * @param  list<string>  $files
     * @param  list<string>  $added
     */
    private function restore(array $files, array $added, string $backupPath): void
    {
        foreach ($files as $file) {
            $target = $this->basePath.DIRECTORY_SEPARATOR.$file;

            if (in_array($file, $added, true)) {
                File::delete($target);
            } elseif (is_file($backupPath.'/'.$file)) {
                File::ensureDirectoryExists(dirname($target));
                File::copy($backupPath.'/'.$file, $target);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function afterFilesChanged(bool $migrate, bool $assets): array
    {
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        $commands = ['optimize:clear' => []];

        if ($migrate) {
            $commands['migrate'] = ['--force' => true];
        }

        if ($assets) {
            $commands['filament:assets'] = [];
        }

        $log = [];

        foreach ($commands as $command => $parameters) {
            try {
                Artisan::call($command, $parameters);
                $log[] = "$ php artisan {$command}\n".trim(Artisan::output());
            } catch (Throwable $e) {
                $log[] = "$ php artisan {$command}\nFAILED: ".$e->getMessage();
            }
        }

        return $log;
    }

    private function safeRelativePath(string $name): string
    {
        $path = str_replace('\\', '/', $name);

        $isUnsafe = $path === ''
            || str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:/', $path)
            || str_contains($path, "\0")
            || in_array('..', explode('/', $path), true);

        if ($isUnsafe) {
            throw new InvalidUpdatePackage("Unsafe path in package: {$name}");
        }

        return $path;
    }

    private function isProtected(string $path): bool
    {
        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
