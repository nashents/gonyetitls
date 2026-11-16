<?php

namespace App\Http\Livewire\Billing;

use App\Models\Company;
use App\Models\SubscriptionBillingContact;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Services\Billing\SubscriptionBillingService;
use App\Services\Billing\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * One client's Gonyeti account: package, license invoices, payments and who
 * the invoices go to. Clients see their own account read-only; system admins
 * (is_admin) open any client's account and manage it from here.
 */
class Account extends Component
{
    public $companyId;
    public $canManage = false;

    // Billing settings
    public $billing_enabled = false;
    public $billing_day = 1;
    public $billing_due_days = 7;
    public $billing_reminder_days = 3;
    public $billing_start_date;

    // Contact form
    public $contact_id;
    public $contact_name;
    public $contact_email;
    public $contact_phonenumber;
    public $contact_send_email = true;
    public $contact_send_whatsapp = true;

    // Payment form
    public $pay_invoice_id;
    public $pay_date;
    public $pay_amount;
    public $pay_method = 'Bank Transfer';
    public $pay_reference;
    public $pay_notes;

    // Manual invoice
    public $invoice_month;

    public function mount($companyId)
    {
        $this->companyId = $companyId;
        $this->canManage = (bool) Auth::user()->is_admin();

        $company = $this->company();
        $this->billing_enabled = (bool) $company->billing_enabled;
        $this->billing_day = $company->billing_day ?: 1;
        $this->billing_due_days = $company->billing_due_days ?? 7;
        $this->billing_reminder_days = $company->billing_reminder_days ?? 3;
        $this->billing_start_date = optional($company->billing_start_date)->toDateString();
        $this->invoice_month = Carbon::today()->format('Y-m');
        $this->pay_date = Carbon::today()->toDateString();
    }

    private function company(): Company
    {
        return Company::findOrFail($this->companyId);
    }

    /** Every write goes through here: only Gonyeti's own admins manage billing. */
    private function authorizeManage(): void
    {
        abort_unless(Auth::user() && Auth::user()->is_admin(), 403);
    }

    private function invoice($id): SubscriptionInvoice
    {
        return SubscriptionInvoice::where('company_id', $this->companyId)->findOrFail($id);
    }

    private function toast(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }

    private function modal(string $id, string $action): void
    {
        $this->dispatchBrowserEvent('billing-modal', ['id' => $id, 'action' => $action]);
    }

    // ---------------- Settings ----------------

    public function saveSettings()
    {
        $this->authorizeManage();
        $this->validate([
            'billing_day' => 'required|integer|min:1|max:31',
            'billing_due_days' => 'required|integer|min:0|max:365',
            'billing_reminder_days' => 'required|integer|min:0|max:365',
            'billing_start_date' => 'nullable|date',
        ]);

        $company = $this->company();

        if ($this->billing_enabled && (float) $company->fee <= 0) {
            $this->addError('billing_enabled', 'Set a license fee on the company before switching billing on.');
            return;
        }

        // Switching billing on without a start date must not back-bill the current month.
        if ($this->billing_enabled && ! $company->billing_enabled && ! $this->billing_start_date) {
            $this->billing_start_date = Carbon::today()->toDateString();
        }

        $company->billing_enabled = (bool) $this->billing_enabled;
        $company->billing_day = (int) $this->billing_day;
        $company->billing_due_days = (int) $this->billing_due_days;
        $company->billing_reminder_days = (int) $this->billing_reminder_days;
        $company->billing_start_date = $this->billing_start_date ?: null;
        $company->save();

        $this->toast('success', 'Billing settings saved.');
    }

    // ---------------- Contacts ----------------

    public function newContact()
    {
        $this->authorizeManage();
        $this->resetContactForm();
        $this->modal('billingContactModal', 'show');
    }

    public function editContact($id)
    {
        $this->authorizeManage();
        $contact = SubscriptionBillingContact::where('company_id', $this->companyId)->findOrFail($id);

        $this->resetErrorBag();
        $this->contact_id = $contact->id;
        $this->contact_name = $contact->name;
        $this->contact_email = $contact->email;
        $this->contact_phonenumber = $contact->phonenumber;
        $this->contact_send_email = $contact->send_email;
        $this->contact_send_whatsapp = $contact->send_whatsapp;
        $this->modal('billingContactModal', 'show');
    }

    public function saveContact(WhatsAppService $whatsapp)
    {
        $this->authorizeManage();
        $this->validate([
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255|required_without:contact_phonenumber',
            'contact_phonenumber' => 'nullable|string|max:30|required_without:contact_email',
        ], [], ['contact_name' => 'name', 'contact_email' => 'email', 'contact_phonenumber' => 'WhatsApp number']);

        if ($this->contact_phonenumber && ! $whatsapp->normalizeNumber($this->contact_phonenumber)) {
            $this->addError('contact_phonenumber', 'Enter a valid WhatsApp number, e.g. 0772123456 or +263772123456.');
            return;
        }

        SubscriptionBillingContact::updateOrCreate(
            ['id' => $this->contact_id, 'company_id' => $this->companyId],
            [
                'name' => $this->contact_name,
                'email' => $this->contact_email ?: null,
                'phonenumber' => $this->contact_phonenumber ?: null,
                'send_email' => (bool) $this->contact_send_email,
                'send_whatsapp' => (bool) $this->contact_send_whatsapp,
            ]
        );

        $this->modal('billingContactModal', 'hide');
        $this->resetContactForm();
        $this->toast('success', 'Billing contact saved.');
    }

    public function toggleContact($id)
    {
        $this->authorizeManage();
        $contact = SubscriptionBillingContact::where('company_id', $this->companyId)->findOrFail($id);
        $contact->status = ! $contact->status;
        $contact->save();
    }

    public function deleteContact($id)
    {
        $this->authorizeManage();
        SubscriptionBillingContact::where('company_id', $this->companyId)->where('id', $id)->delete();
        $this->toast('success', 'Billing contact removed.');
    }

    private function resetContactForm(): void
    {
        $this->resetErrorBag();
        $this->contact_id = null;
        $this->contact_name = '';
        $this->contact_email = '';
        $this->contact_phonenumber = '';
        $this->contact_send_email = true;
        $this->contact_send_whatsapp = true;
    }

    // ---------------- Invoices ----------------

    public function generateInvoice(SubscriptionBillingService $billing)
    {
        $this->authorizeManage();
        $this->validate(['invoice_month' => 'required|date_format:Y-m']);

        $company = $this->company();
        if ((float) $company->fee <= 0) {
            $this->toast('error', 'This company has no license fee set.');
            return;
        }

        $date = $billing->billingDate($company, Carbon::createFromFormat('Y-m-d', $this->invoice_month . '-01'));
        $invoice = $billing->createInvoice($company, $date, Auth::id());

        if (! $invoice) {
            $this->toast('error', $date->format('F Y') . ' has already been invoiced for this company.');
            return;
        }

        $this->toast('success', "Invoice {$invoice->invoice_number} raised. Use Send to deliver it now; otherwise the next billing run sends it.");
    }

    public function sendInvoice(SubscriptionBillingService $billing, $id, $type = 'invoice')
    {
        $this->authorizeManage();
        $invoice = $this->invoice($id);
        $type = $type === 'reminder' ? 'reminder' : 'invoice';

        if ($invoice->status === SubscriptionInvoice::STATUS_CANCELLED) {
            $this->toast('error', 'This invoice is cancelled.');
            return;
        }

        $result = $billing->send($invoice, $type);

        if ($result['sent'] === 0 && $result['failed'] === 0) {
            $this->toast('error', 'Nothing was sent: this company has no billing contact with an email or WhatsApp number.');
        } elseif ($result['failed'] > 0) {
            $this->toast($result['sent'] > 0 ? 'warning' : 'error', "Sent {$result['sent']}, failed {$result['failed']} - see the delivery log.");
        } else {
            $this->toast('success', ($type === 'reminder' ? 'Reminder' : 'Invoice') . " sent to {$result['sent']} recipient(s).");
        }
    }

    public function cancelInvoice(SubscriptionBillingService $billing, $id)
    {
        $this->authorizeManage();
        $billing->cancel($this->invoice($id));
        $this->toast('success', 'Invoice cancelled.');
    }

    // ---------------- Payments ----------------

    public function openPayment($id)
    {
        $this->authorizeManage();
        $invoice = $this->invoice($id);

        $this->resetErrorBag();
        $this->pay_invoice_id = $invoice->id;
        $this->pay_date = Carbon::today()->toDateString();
        $this->pay_amount = $invoice->balance;
        $this->pay_method = 'Bank Transfer';
        $this->pay_reference = '';
        $this->pay_notes = '';
        $this->modal('billingPaymentModal', 'show');
    }

    public function savePayment(SubscriptionBillingService $billing)
    {
        $this->authorizeManage();
        $this->validate([
            'pay_invoice_id' => 'required',
            'pay_date' => 'required|date|before_or_equal:today',
            'pay_amount' => 'required|numeric|min:0.01',
            'pay_method' => 'nullable|string|max:50',
            'pay_reference' => 'nullable|string|max:255',
            'pay_notes' => 'nullable|string|max:1000',
        ], [], ['pay_date' => 'payment date', 'pay_amount' => 'amount', 'pay_method' => 'method', 'pay_reference' => 'reference']);

        $invoice = $this->invoice($this->pay_invoice_id);

        $billing->recordPayment($invoice, [
            'date' => $this->pay_date,
            'amount' => $this->pay_amount,
            'method' => $this->pay_method,
            'reference' => $this->pay_reference ?: null,
            'notes' => $this->pay_notes ?: null,
        ], Auth::id());

        $this->modal('billingPaymentModal', 'hide');
        $this->pay_invoice_id = null;
        $this->toast('success', "Payment recorded against {$invoice->invoice_number}.");
    }

    public function deletePayment(SubscriptionBillingService $billing, $id)
    {
        $this->authorizeManage();
        $billing->deletePayment(SubscriptionPayment::where('company_id', $this->companyId)->findOrFail($id));
        $this->toast('success', 'Payment removed and the invoice balance updated.');
    }

    public function render(SubscriptionBillingService $billing, WhatsAppService $whatsapp)
    {
        $company = Company::with(['license_currency', 'currency'])->findOrFail($this->companyId);

        $invoices = SubscriptionInvoice::where('company_id', $company->id)
            ->with('currency')
            ->orderByDesc('issue_date')->orderByDesc('id')
            ->get();

        return view('livewire.billing.account', [
            'company' => $company,
            'currency' => $company->license_currency ?? $company->currency,
            'invoices' => $invoices,
            'payments' => SubscriptionPayment::where('company_id', $company->id)
                ->with(['invoice.currency', 'recorder'])
                ->orderByDesc('date')->orderByDesc('id')
                ->get(),
            'contacts' => $company->billing_contacts()->orderBy('name')->get(),
            'outstanding' => $invoices->filter->isOutstanding()->sum('balance'),
            'overdue' => $invoices->filter->isOverdue()->sum('balance'),
            'nextInvoiceDate' => $billing->nextInvoiceDate($company),
            'payInvoice' => $this->pay_invoice_id ? $invoices->firstWhere('id', $this->pay_invoice_id) : null,
            'deliveries' => $this->canManage
                ? \App\Models\SubscriptionNotification::whereIn('subscription_invoice_id', $invoices->pluck('id'))
                    ->with('invoice')->orderByDesc('id')->limit(25)->get()
                : collect(),
            'whatsappReady' => $whatsapp->isConfigured(),
        ]);
    }
}
