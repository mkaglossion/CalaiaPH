<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanonicalHostMiddleware
{
    protected array $wwwRedirectMap;

    public function __construct()
    {
        $this->wwwRedirectMap = array_values(array_filter(array_map(
            static fn (string $host): string => rtrim($host, '/'),
            array_map('trim', explode(',', (string) config('security.www_redirect_map', 'www.calaia.ph')))
        )));
    }

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        foreach ($this->wwwRedirectMap as $wwwHost) {
            if ($host === $wwwHost) {
                $targetHost = preg_replace('/^www\./', '', $wwwHost, 1);

                if ($targetHost !== $wwwHost) {
                    $scheme = $request->isSecure() || str_starts_with((string) config('app.url'), 'https') ? 'https' : 'http';
                    $redirectUrl = $scheme . '://' . $targetHost . $request->getRequestUri();

                    return redirect($redirectUrl, 301);
                }
            }
        }

        return $next($request);
    }
}
