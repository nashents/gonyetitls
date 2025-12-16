<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class CheckCompanyStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $auth_user = auth()->user();
        $user = User::with('employee.company')->find($auth_user?->id);

        if ($user) {

            // Optional: update last activity
            // if the column exists on users table
            $user->last_activity_at = now();
            $user->saveQuietly();

            // Skip if needed (e.g. super admin)
            if (in_array($user->category, ['employee', 'driver', 'admin'])) {

                $company = optional(optional($user->employee)->company);

                // Company inactive or missing
                if (!$company || $company->status != "1") {
                    auth()->logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()
                        ->route('login')
                        ->withErrors([
                            'company' => 'Company account suspended. Please contact support.',
                        ]);
                }
            }

            // You can also add a per-user active flag check here if needed
        }
        return $next($request);
    }
}
