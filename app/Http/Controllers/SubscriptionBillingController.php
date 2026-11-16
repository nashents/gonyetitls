<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Services\Billing\SubscriptionBillingService;
use Illuminate\Support\Facades\Auth;

/**
 * Gonyeti subscription billing screens.
 *  - index / company: Gonyeti's own admins (users.is_admin) across all clients
 *  - account: a client's Admin / Super Admin, for their own company, read-only
 */
class SubscriptionBillingController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->is_admin(), 403);

        return view('billing.index');
    }

    public function company(Company $company)
    {
        abort_unless(Auth::user()->is_admin(), 403);
        abort_if($company->isSystemOwner(), 404);

        return view('billing.account', ['company' => $company, 'managing' => true]);
    }

    public function account()
    {
        $company = $this->ownCompany();
        abort_unless($company && $this->canViewOwnAccount(), 403);

        // Gonyeti itself has no account to show - its admins work from the client list.
        if ($company->isSystemOwner()) {
            return Auth::user()->is_admin() ? redirect()->route('billing.index') : abort(404);
        }

        return view('billing.account', ['company' => $company, 'managing' => false]);
    }

    public function pdf(SubscriptionInvoice $invoice, SubscriptionBillingService $billing)
    {
        $ownInvoice = $this->canViewOwnAccount() && $this->ownCompany()?->id === $invoice->company_id;
        abort_unless(Auth::user()->is_admin() || $ownInvoice, 403);

        return response($billing->pdf($invoice), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $billing->pdfFilename($invoice) . '"',
        ]);
    }

    public function receipt(SubscriptionPayment $payment, SubscriptionBillingService $billing)
    {
        $ownPayment = $this->canViewOwnAccount() && $this->ownCompany()?->id === $payment->company_id;
        abort_unless(Auth::user()->is_admin() || $ownPayment, 403);
        abort_unless($payment->invoice, 404);

        return response($billing->receiptPdf($payment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Gonyeti-Receipt-' . $billing->receiptNumber($payment) . '.pdf"',
        ]);
    }

    private function ownCompany(): ?Company
    {
        $user = Auth::user();

        return $user->employee?->company ?? $user->company;
    }

    private function canViewOwnAccount(): bool
    {
        $user = Auth::user();

        return $user->is_admin() || $user->roles->pluck('name')->intersect(['Super Admin', 'Admin'])->isNotEmpty();
    }
}
