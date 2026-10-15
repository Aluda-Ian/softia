<?php

namespace Tests\Feature;

use App\Enums\UpdateStatus;
use App\Models\User;
use App\Support\Updates\InvalidUpdatePackage;
use App\Support\Updates\UpdateInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class UpdateInstallerTest extends TestCase
{
    use RefreshDatabase;

    private string $site;

    private string $packages;

    protected function setUp(): void
    {
        parent::setUp();

        // A throwaway "site" so tests never touch the real project files.
        $this->site = storage_path('framework/testing/update-site-'.uniqid());
        $this->packages = storage_path('framework/testing/update-packages-'.uniqid());
        File::ensureDirectoryExists($this->site.'/resources/views');
        File::ensureDirectoryExists($this->packages);
        File::put($this->site.'/resources/views/hello.blade.php', 'old version');
        File::put($this->site.'/.env', 'APP_KEY=keep-me');

        Artisan::partialMock()->shouldReceive('call')->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('ok');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->site);
        File::deleteDirectory($this->packages);

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $files
     * @param  array<string, mixed>|null  $manifest
     */
    private function package(array $files, ?array $manifest = ['version' => '1.1.0', 'migrations' => false]): string
    {
        $path = $this->packages.'/'.uniqid().'.zip';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE);

        foreach ($files as $name => $contents) {
            $zip->addFromString($name, $contents);
        }

        if ($manifest !== null) {
            $zip->addFromString(UpdateInstaller::MANIFEST, json_encode($manifest));
        }

        $zip->close();

        return $path;
    }

    public function test_installs_files_skips_protected_paths_and_rolls_back(): void
    {
        $installer = new UpdateInstaller($this->site);
        $zip = $this->package([
            'resources/views/hello.blade.php' => 'new version',
            'app/NewFeature.php' => '<?php // new',
            '.env' => 'APP_KEY=stolen',
        ]);

        $update = $installer->stage($zip);
        $this->assertSame(['resources/views/hello.blade.php', 'app/NewFeature.php'], $update->files);

        $installer->apply($update);

        $this->assertSame(UpdateStatus::Applied, $update->fresh()->status);
        $this->assertSame('new version', File::get($this->site.'/resources/views/hello.blade.php'));
        $this->assertFileExists($this->site.'/app/NewFeature.php');
        $this->assertSame('APP_KEY=keep-me', File::get($this->site.'/.env'));

        $installer->rollback($update->fresh());

        $this->assertSame(UpdateStatus::RolledBack, $update->fresh()->status);
        $this->assertSame('old version', File::get($this->site.'/resources/views/hello.blade.php'));
        $this->assertFileDoesNotExist($this->site.'/app/NewFeature.php');

        File::deleteDirectory($update->fresh()->backup_path);
    }

    public function test_rejects_packages_without_a_manifest(): void
    {
        $this->expectException(InvalidUpdatePackage::class);

        (new UpdateInstaller($this->site))->stage($this->package(['app/X.php' => 'x'], manifest: null));
    }

    public function test_rejects_path_traversal(): void
    {
        $this->expectException(InvalidUpdatePackage::class);

        (new UpdateInstaller($this->site))->stage($this->package(['../outside.php' => 'x']));
    }

    public function test_only_super_admins_reach_the_maintenance_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/admin/maintenance')->assertForbidden();
    }
}
