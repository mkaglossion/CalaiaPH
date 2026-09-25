<?php

namespace Tests;

use App\Services\TwoFactorSessionService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

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

    protected function postWithCsrf(string $uri, array $data = [], array $session = [], ?string $from = null)
    {
        $token = Str::random(40);
        $request = $from ? $this->from($from) : $this;

        return $request
            ->withSession(array_merge($session, ['_token' => $token]))
            ->withHeaders(['X-CSRF-TOKEN' => $token])
            ->post($uri, array_merge(['_token' => $token], $data));
    }

    protected function postJsonWithCsrf(string $uri, array $data = [], array $session = [], ?string $from = null)
    {
        $token = Str::random(40);
        $request = $from ? $this->from($from) : $this;

        return $request
            ->withSession(array_merge($session, ['_token' => $token]))
            ->withHeaders(['X-CSRF-TOKEN' => $token])
            ->postJson($uri, array_merge(['_token' => $token], $data));
    }

    public function actingAs($user, $guard = null)
    {
        parent::actingAs($user, $guard);

        return $this->withSession([
            TwoFactorSessionService::VERIFIED_USER_ID => $user->getAuthIdentifier(),
        ]);
    }
}
