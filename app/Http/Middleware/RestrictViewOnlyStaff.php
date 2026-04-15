<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictViewOnlyStaff
{
    /**
     * View-only "staff" role: allow GET/HEAD and a small set of mutating routes (logout, pin, notifications, profile).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->isViewOnlyStaff()) {
            return $next($request);
        }

        if (in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $name = $request->route()?->getName();
        $allowedNames = [
            'logout',
            'user.verify.pin',
            'user.update.pin',
            'notifications.get',
            'notifications.count',
            'notifications.read',
            'notifications.read-all',
            'profile.update',
            'password.update',
            'pr.store',
            'pr.generate-number',
        ];

        if ($name && in_array($name, $allowedNames, true)) {
            return $next($request);
        }

        $path = trim($request->path(), '/');
        if (in_array($path, ['keep-alive', 'check-session-status'], true)) {
            return $next($request);
        }

        abort(403, 'View-only access: you cannot modify data.');
    }
}
