<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function index()
    {
        return view('user.auth.login');
    }

    public function store(LoginRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません'
        ]);
    }
}
