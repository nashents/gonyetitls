<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Override so the 'customer' guard's failures redirect to the portal
     * login instead of the staff login - redirectTo() alone can't tell
     * which guard failed.
     */
    protected function unauthenticated($request, array $guards)
    {
        if (in_array('customer', $guards, true)) {
            throw new \Illuminate\Auth\AuthenticationException(
                'Unauthenticated.', $guards, route('customer.login')
            );
        }

        parent::unauthenticated($request, $guards);
    }
}
