<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $url = match ($user->role) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'staff'                => route('staff.dashboard'),
            'customer'             => route('customer.dashboard'),
            default                => route('admin.dashboard'),
        };

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($url);
    }
}
