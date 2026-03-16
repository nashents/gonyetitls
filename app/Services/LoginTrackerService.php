<?php

namespace App\Services;

use App\Models\User;
use Stevebauman\Location\Facades\Location;

class LoginTrackerService
{
    public function record(User $user, string $ip): void
    {
        $position = Location::get($ip);   // returns false on local/private IPs

        $user->update([
            'last_login_at'           => now(),
            'last_login_ip'           => $ip,
            'last_login_city'         => $position ? $position->cityName       : null,
            'last_login_country_code' => $position ? $position->countryCode    : null,
            'last_login_country'      => $position ? $position->countryName    : null,
        ]);
    }
}