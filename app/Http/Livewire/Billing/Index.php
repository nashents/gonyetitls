<?php

namespace App\Http\Livewire\Billing;

use App\Models\Company;
use App\Models\SubscriptionInvoice;
use App\Services\Billing\SubscriptionBillingService;
use App\Services\Billing\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Gonyeti admins' overview of client billing: every client's package and
 * balance, plus all license invoices. Per-client work (payments, contacts,
 * settings) happens on Billing\Account.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $status = '';
    public $company_filter = '';

    public $payment_details;

    public function mount(SubscriptionBillingService $billing)
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $this->payment_details = $billing->issuer()?->billing_payment_details;
    }

    /** How clients should pay (bank details etc.) - printed on every license invoice. */
    public function savePaymentDetails(SubscriptionBillingService $billing)
    {
        abort_unless(Auth::user()->is_admin(), 403);
        $this->validate(['payment_details' => 'nullable|string|max:2000']);

        $issuer = $billing->issuer();
        if (! $issuer) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'No Admin company found to store the payment details on.']);
            return;
        }

        $issuer->billing_payment_details = trim((string) $this->payment_details) ?: null;
        $issuer->save();

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Payment details saved - they print on every invoice from now on.']);
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedCompanyFilter()
    {
        $this->resetPage();
    }

    public function runBilling(SubscriptionBillingService $billing)
    {
        abort_unless(Auth::user()->is_admin(), 403);

        $result = $billing->runDaily(true);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Billing run complete: {$result['generated']} invoice(s) raised, {$result['sent']} delivered, {$result['reminders']} reminder(s) sent.",
        ]);
    }

    public function render(SubscriptionBillingService $billing, WhatsAppService $whatsapp)
    {
        $companies = Company::where(fn ($q) => $q->whereNull('type')->orWhere('type', '!=', 'Admin'))
            ->with(['license_currency', 'currency'])
            ->withSum(['subscription_invoices as outstanding_balance' => fn ($q) => $q->outstanding()], 'balance')
            ->withSum(['subscription_invoices as overdue_balance' => fn ($q) => $q->outstanding()->whereDate('due_date', '<', now()->toDateString())], 'balance')
            ->orderBy('name')
            ->get();

        $invoices = SubscriptionInvoice::with(['company', 'currency'])
            ->when($this->company_filter, fn ($q) => $q->where('company_id', $this->company_filter))
            ->when($this->status === 'overdue', fn ($q) => $q->outstanding()->whereDate('due_date', '<', now()->toDateString()))
            ->when($this->status === 'outstanding', fn ($q) => $q->outstanding())
            ->when(in_array($this->status, ['paid', 'cancelled'], true), fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('issue_date')->orderByDesc('id')
            ->paginate(25);

        return view('livewire.billing.index', [
            'companies' => $companies,
            'invoices' => $invoices,
            'nextDates' => $companies->mapWithKeys(fn ($c) => [$c->id => $billing->nextInvoiceDate($c)]),
            'whatsappReady' => $whatsapp->isConfigured(),
        ]);
    }
}
