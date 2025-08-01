<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse;

class AdminLogoutResponse implements LogoutResponse
{
    public function toResponse($request)
    {
        return redirect()->route('admin.login.form');
    }
}
