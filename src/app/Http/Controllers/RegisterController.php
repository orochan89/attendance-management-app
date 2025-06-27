<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{

    public function index()
    {
        return view('user.auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $user = User::created([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return redirect()->route('user.login.form');
    }
}
