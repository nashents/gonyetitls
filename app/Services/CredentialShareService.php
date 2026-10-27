<?php

namespace App\Services;

use App\Mail\AccountCreationMail;
use App\Models\CredentialShare;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Sends employee/driver login details and logs who sent them. Only users
 * allowed by User::canShareCredentials() (is_admin or the Business Settings
 * allow-list) may share.
 */
class CredentialShareService
{
    public function email(Employee $employee, bool $bulk = false): bool
    {
        $this->authorize();

        $user = $employee->user;
        if (!$user || empty($employee->email) || !filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        Mail::to($employee->email)->send(new AccountCreationMail($user, $employee->company, $employee->pin));
        $user->sent_credentials = True;
        $user->update();

        $this->log($employee, 'email', $employee->email, $bulk);

        return true;
    }

    /** WhatsApp links open client-side; this records the click. */
    public function logWhatsapp(Employee $employee): void
    {
        $this->authorize();
        $this->log($employee, 'whatsapp', $employee->user?->phonenumber ?? $employee->phonenumber, false);
    }

    private function authorize(): void
    {
        abort_unless(Auth::user()?->canShareCredentials(), 403, 'You are not allowed to share login credentials.');
    }

    private function log(Employee $employee, string $channel, ?string $recipient, bool $bulk): void
    {
        CredentialShare::create([
            'employee_id'  => $employee->id,
            'user_id'      => $employee->user?->id,
            'shared_by_id' => Auth::id(),
            'channel'      => $channel,
            'recipient'    => $recipient,
            'bulk'         => $bulk,
            'ip_address'   => request()->ip(),
        ]);
    }
}
