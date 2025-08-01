<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse;

class UserLogoutResponse implements LogoutResponse
{
    public function toResponse($request)
    {
        return redirect()->route('login.form');
    }
}
