<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ensure tests run with maintenance mode disabled by default.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure admin.settings cache doesn't enable maintenance during tests
        try {
            cache()->put('admin.settings', array_merge(cache()->get('admin.settings', []), ['system_maintenance' => false]));
        } catch (\Exception $e) {
            // ignore cache issues in certain environments
        }
    }
}
