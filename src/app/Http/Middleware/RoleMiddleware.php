<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login.index');
        }

        if (Auth::user()->role !== $role) {
            abort(403, 'このページにアクセスする権限がありません。');
        }

        return $next($request);
    }
}
