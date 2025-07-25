<?php

namespace App\Actions\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, '管理者のみがアクセス可能です。');
        }

        return $next($request);
    }
}
