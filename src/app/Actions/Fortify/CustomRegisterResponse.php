<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse;

class CustomRegisterResponse implements RegisterResponse
{
    public function toResponse($request)
    {
        return redirect('/attendance');
    }
}
