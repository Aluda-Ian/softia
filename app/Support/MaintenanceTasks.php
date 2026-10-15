<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Whitelisted Artisan commands that can be triggered from the browser
 * on hosts without terminal access.
 */
class MaintenanceTasks
{
    /**
     * @return array<string, array{label: string, description: string, command: string, parameters: array<string, mixed>}>
     */
    public static function all(): array
    {
        return [
            'migrate' => [
                'label' => 'Run database migrations',
                'description' => 'Applies new database changes. Run after every upload that includes migrations.',
                'command' => 'migrate',
                'parameters' => ['--force' => true],
            ],
            'seed' => [
                'label' => 'Load starter content',
                'description' => 'New site only: adds the product lines, starter products, pages and staff teams. Re-running resets starter product details.',
                'command' => 'db:seed',
                'parameters' => ['--force' => true],
            ],
            'optimize' => [
                'label' => 'Build caches',
                'description' => 'Caches config, routes and views for speed. Run after every upload.',
                'command' => 'optimize',
                'parameters' => [],
            ],
            'optimize-clear' => [
                'label' => 'Clear all caches',
                'description' => 'Use after editing .env or when changes do not show up.',
                'command' => 'optimize:clear',
                'parameters' => [],
            ],
            'filament-assets' => [
                'label' => 'Publish admin assets',
                'description' => 'Copies Filament CSS/JS into public/. Run after updating Filament.',
                'command' => 'filament:assets',
                'parameters' => [],
            ],
        ];
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * @return array{ok: bool, output: string}
     */
    public static function run(string $key): array
    {
        $task = self::all()[$key];

        try {
            $exitCode = Artisan::call($task['command'], $task['parameters']);

            return [
                'ok' => $exitCode === 0,
                'output' => trim(Artisan::output()) ?: 'Done.',
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'ok' => false,
                'output' => $e->getMessage(),
            ];
        }
    }
}
