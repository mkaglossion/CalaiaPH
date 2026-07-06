<?php

namespace Tests\Unit;

use App\Helpers\VersionHelper;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        File::delete(storage_path('app/version.txt'));
        VersionHelper::resetCache();

        parent::tearDown();
    }

    public function test_full_uses_stored_version_file_when_present(): void
    {
        $path = storage_path('app/version.txt');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, "9.8.7\n");

        VersionHelper::resetCache();

        $this->assertSame('9.8.7', VersionHelper::full());
        $this->assertSame('v9.8.7', VersionHelper::display());
    }
}
