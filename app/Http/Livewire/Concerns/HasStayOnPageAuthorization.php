<?php

namespace App\Http\Livewire\Concerns;

/**
 * Shared "stay on this page instead of redirecting" toggle for the
 * authorization (approve/reject) modals on every Pending list. Default
 * (unchecked) preserves the existing system-wide behavior of redirecting to
 * the Approved/Rejected list once a decision is saved; checking it keeps the
 * user on the Pending list so they can authorize the next item without
 * navigating back and forth.
 */
trait HasStayOnPageAuthorization
{
    public bool $stay_on_page = false;

    /** Redirect to $route unless the user opted to stay on this page. */
    private function redirectOrStay(string $route, array $params = [])
    {
        return $this->stay_on_page ? null : redirect()->route($route, $params);
    }
}
