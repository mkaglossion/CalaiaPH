<?php

namespace Tests\Unit;

use App\Helpers\VersionHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class VersionHelperTest extends TestCase
{
    private bool $storedVersionExisted = false;

    private ?string $storedVersionContents = null;

    protected function setUp(): void
    {
        parent::setUp();

        $path = storage_path('app/version.txt');
        $this->storedVersionExisted = File::exists($path);
        $this->storedVersionContents = $this->storedVersionExisted
            ? File::get($path)
            : null;
    }

    protected function tearDown(): void
    {
        $path = storage_path('app/version.txt');

        if ($this->storedVersionExisted) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $this->storedVersionContents);
        } else {
            File::delete($path);
        }

        VersionHelper::resetCache();

        parent::tearDown();
    }

    public function test_full_prefers_current_git_version_over_stale_stored_version(): void
    {
        Process::fake([
            '*' => Process::result(output: 'v1.1.0-38-ge745862'),
        ]);

        $path = storage_path('app/version.txt');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, "1.1.19\n");

        VersionHelper::resetCache();

        $this->assertSame('1.1.38', VersionHelper::full());
        $this->assertSame('v1.1.38', VersionHelper::display());
    }

    public function test_full_uses_stored_version_when_git_metadata_is_unavailable(): void
    {
        Process::fake([
            '*' => Process::result(errorOutput: 'Git metadata unavailable', exitCode: 128),
        ]);

        $path = storage_path('app/version.txt');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, "9.8.7\n");

        VersionHelper::resetCache();

        $this->assertSame('9.8.7', VersionHelper::full());
        $this->assertSame('v9.8.7', VersionHelper::display());
    }

    public function test_sync_stores_the_current_git_version(): void
    {
        Process::fake([
            '*' => Process::result(output: 'v2.4.3-5-gabcdef1'),
        ]);

        $this->assertSame('2.4.8', VersionHelper::syncStoredVersion());
        $this->assertSame('2.4.8' . PHP_EOL, File::get(storage_path('app/version.txt')));
    }
}
