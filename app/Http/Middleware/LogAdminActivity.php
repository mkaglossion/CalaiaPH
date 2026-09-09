<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $shouldAudit = $user
            && $user->user_type === 'admin'
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        if (! $shouldAudit || ! Schema::hasTable('admin_activity_logs')) {
            return $next($request);
        }

        $latestLogId = (int) (AdminActivityLog::max('id') ?? 0);
        $response = $next($request);

        if (
            $response->getStatusCode() >= 400
            || $request->session()->has('errors')
            || $request->session()->has('error')
        ) {
            return $response;
        }

        $alreadyLogged = AdminActivityLog::query()
            ->where('id', '>', $latestLogId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $alreadyLogged) {
            $routeName = (string) ($request->route()?->getName() ?? 'admin.action');
            $action = $this->resolveAction($request, $routeName);

            AdminActivityLog::log(
                $action,
                $this->describeRoute($routeName, $action),
                null,
                [
                    'route' => $routeName,
                    'method' => $request->method(),
                ]
            );
        }

        return $response;
    }

    private function resolveAction(Request $request, string $routeName): string
    {
        $lastSegment = collect(explode('.', $routeName))->last();

        if (in_array($lastSegment, ['destroy', 'delete', 'clear'], true)) {
            return 'admin_delete';
        }

        if (in_array($lastSegment, ['store', 'create', 'upload'], true)) {
            return 'admin_create';
        }

        if (in_array($lastSegment, [
            'update', 'quickset', 'bulk', 'bulk-update', 'block', 'unblock',
            'initialize', 'resolve', 'complete', 'cancel', 'confirm', 'record-payment',
        ], true)) {
            return 'admin_update';
        }

        return match ($request->method()) {
            'DELETE' => 'admin_delete',
            'PUT', 'PATCH' => 'admin_update',
            default => 'admin_action',
        };
    }

    private function describeRoute(string $routeName, string $action): string
    {
        $ignoredSegments = [
            'admin', 'store', 'create', 'update', 'destroy', 'delete', 'clear',
        ];
        $resource = collect(explode('.', $routeName))
            ->reject(fn (string $segment) => in_array($segment, $ignoredSegments, true))
            ->map(fn (string $segment) => str_replace(['-', '_'], ' ', $segment))
            ->implode(' ');
        $resource = str($resource !== '' ? $resource : 'admin action')->squish()->title()->toString();

        $verb = match ($action) {
            'admin_create' => 'Created',
            'admin_update' => 'Updated',
            'admin_delete' => 'Deleted',
            default => 'Completed',
        };

        return "{$verb} {$resource}";
    }
}
