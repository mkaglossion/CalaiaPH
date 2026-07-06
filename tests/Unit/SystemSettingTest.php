<?php

namespace Tests\Unit;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    public function test_current_returns_null_when_schema_check_fails(): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('system_settings')
            ->andThrow(new \RuntimeException('DB unavailable'));

        $this->assertNull(SystemSetting::current());
    }

    public function test_cached_data_returns_defaults_when_schema_check_fails(): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('system_settings')
            ->andThrow(new \RuntimeException('DB unavailable'));

        $this->assertSame(SystemSetting::defaults(), SystemSetting::cachedData());
    }
}
