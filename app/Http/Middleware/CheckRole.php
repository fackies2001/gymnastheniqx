<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = $user->role->role_name ?? null;

        // If no role assigned, deny access
        if (!$userRole) {
            abort(403, 'No role assigned. Please contact administrator.');
        }

        $allowed = [];
        foreach ($roles as $param) {
            foreach (preg_split('/\s*,\s*/', (string) $param) as $piece) {
                $piece = strtolower(trim($piece));
                if ($piece !== '') {
                    $allowed[] = $piece;
                }
            }
        }
        $allowed = array_values(array_unique($allowed));

        $userNorm = strtolower(trim($userRole));

        if (!in_array($userNorm, $allowed, true)) {
            abort(403, 'Unauthorized access. You do not have permission to access this page.');
        }

        return $next($request);
    }
}
