<?php

namespace App\Services\Billing;

use App\Mail\SubscriptionInvoiceMail;
use App\Models\Company;
use App\Models\SubscriptionBillingContact;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionNotification;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Gonyeti's subscription billing of its client companies:
 *  - raises one license invoice per client per month on the client's billing day
 *  - delivers it by email and WhatsApp to the client's billing contacts
 *  - reminds them every billing_reminder_days until a payment clears the balance
 *  - records payments and keeps invoice balances current
 *
 * runDaily() is safe to call as often as you like: the unique
 * (company_id, period) index stops duplicate invoices, sent_at stops duplicate
 * sends and last_reminder_at paces the reminders.
 */
class SubscriptionBillingService
{
    /** Give up re-sending a new invoice after this many fully failed attempts. */
    public const MAX_SEND_ATTEMPTS = 3;

    public function __construct(private WhatsAppService $whatsapp)
    {
    }

    /**
     * The whole daily cycle. Returns null when another run is already in
     * progress (cron and the web-traffic trigger can overlap).
     */
    public function runDaily(bool $force = false): ?array
    {
        if (! $force && ! Cache::add('subscription-billing:running', now()->toDateTimeString(), now()->addMinutes(10))) {
            return null;
        }

        try {
            $generated = $this->generateDueInvoices();

            $sent = 0;
            SubscriptionInvoice::outstanding()
                ->whereNull('sent_at')
                ->where('send_attempts', '<', self::MAX_SEND_ATTEMPTS)
                ->get()
                ->each(function (SubscriptionInvoice $invoice) use (&$sent) {
                    $sent += $this->send($invoice)['sent'] > 0 ? 1 : 0;
                });

            return [
                'generated' => $generated->count(),
                'sent' => $sent,
                'reminders' => $this->sendReminders(),
            ];
        } finally {
            Cache::forget('subscription-billing:running');
        }
    }

    // ------------------------------------------------------------------
    // Invoice generation
    // ------------------------------------------------------------------

    /** Companies that are billed automatically. */
    public function billableCompanies()
    {
        return Company::query()
            ->where('billing_enabled', true)
            ->where('status', 1)
            ->where(fn ($q) => $q->whereNull('type')->orWhere('type', '!=', 'Admin'));
    }

    /** The client's billing date in $month (day 31 falls back to the month's last day). */
    public function billingDate(Company $company, Carbon $month): Carbon
    {
        $day = min(max((int) $company->billing_day, 1), $month->daysInMonth);

        return $month->copy()->startOfDay()->day($day);
    }

    /** When the next automatic invoice will be raised, or null if billing is off. */
    public function nextInvoiceDate(Company $company, ?Carbon $today = null): ?Carbon
    {
        if (! $company->billing_enabled || (float) $company->fee <= 0) {
            return null;
        }

        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $month = $today->copy()->startOfMonth();

        // Look far enough ahead to pass the start date and any month already invoiced.
        for ($i = 0; $i < 24; $i++) {
            $date = $this->billingDate($company, $month);
            $invoiced = SubscriptionInvoice::where('company_id', $company->id)->where('period', $date->format('Y-m'))->exists();
            $beforeStart = $company->billing_start_date && $date->lt($company->billing_start_date);

            if (! $invoiced && ! $beforeStart) {
                return $date->lt($today) ? $today : $date;
            }
            $month->addMonthNoOverflow();
        }

        return null;
    }

    /** Raise this month's invoice for every client whose billing day has arrived. */
    public function generateDueInvoices(?Carbon $today = null): Collection
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $created = collect();

        foreach ($this->billableCompanies()->get() as $company) {
            $date = $this->billingDate($company, $today);

            if ($today->lt($date) || (float) $company->fee <= 0) {
                continue;
            }
            if ($company->billing_start_date && $date->lt($company->billing_start_date)) {
                continue;
            }

            try {
                if ($invoice = $this->createInvoice($company, $date)) {
                    $created->push($invoice);
                }
            } catch (\Throwable $e) {
                Log::warning("Subscription billing: could not invoice company #{$company->id}: " . $e->getMessage());
            }
        }

        return $created;
    }

    /**
     * Create the invoice for the billing month containing $billingDate. Returns
     * null when that month has already been invoiced.
     */
    public function createInvoice(Company $company, Carbon $billingDate, ?int $generatedBy = null): ?SubscriptionInvoice
    {
        $billingDate = $billingDate->copy()->startOfDay();
        $period = $billingDate->format('Y-m');

        if (SubscriptionInvoice::where('company_id', $company->id)->where('period', $period)->exists()) {
            return null;
        }

        $amount = round((float) $company->fee, 2);
        $periodEnd = $billingDate->copy()->addMonthNoOverflow()->subDay();

        try {
            return DB::transaction(function () use ($company, $billingDate, $period, $periodEnd, $amount, $generatedBy) {
                $invoice = SubscriptionInvoice::create([
                    'company_id' => $company->id,
                    'period' => $period,
                    'period_start' => $billingDate,
                    'period_end' => $periodEnd,
                    'issue_date' => $billingDate,
                    'due_date' => $billingDate->copy()->addDays((int) $company->billing_due_days),
                    'plan' => $company->plan,
                    'description' => 'Gonyeti TLS license fee (' . $company->plan_label . ') ' . $billingDate->format('d M Y') . ' - ' . $periodEnd->format('d M Y'),
                    'currency_id' => $company->license_currency_id ?: $company->currency_id,
                    'amount' => $amount,
                    'amount_paid' => 0,
                    'balance' => $amount,
                    'status' => SubscriptionInvoice::STATUS_UNPAID,
                    'generated_by' => $generatedBy,
                ]);

                $invoice->invoice_number = 'GINV-' . str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT);
                $invoice->save();

                return $invoice;
            });
        } catch (QueryException $e) {
            // Lost a race with another run on the unique (company_id, period) index.
            if (SubscriptionInvoice::where('company_id', $company->id)->where('period', $period)->exists()) {
                return null;
            }
            throw $e;
        }
    }

    // ------------------------------------------------------------------
    // Delivery
    // ------------------------------------------------------------------

    /**
     * Who gets this client's invoices: its active billing contacts, or the
     * company's own email / phone number when no contact list has been set.
     */
    public function recipients(Company $company): Collection
    {
        $contacts = $company->billing_contacts()->where('status', true)->get();

        if ($contacts->isEmpty() && ($company->email || $company->phonenumber)) {
            $contacts = collect([new SubscriptionBillingContact([
                'name' => $company->name,
                'email' => $company->email,
                'phonenumber' => $company->phonenumber,
                'send_email' => true,
                'send_whatsapp' => true,
            ])]);
        }

        return $contacts;
    }

    /** Gonyeti itself - the "from" side of every license invoice. */
    public function issuer(): ?Company
    {
        return Company::where('type', 'Admin')->orderBy('id')->first();
    }

    public function pdf(SubscriptionInvoice $invoice): string
    {
        $invoice->loadMissing(['company', 'currency']);
        $issuer = $this->documentIssuer();
        $document = new ClassicInvoiceDocument($invoice, $issuer);

        // Same wrapper + CLASSIC layout partial as the sales invoices (see InvoicePdf).
        return app()->make('dompdf.wrapper')
            ->loadView('invoices.pdf', [
                'layout' => 'classic',
                'invoice' => $document,
                'company' => $issuer,
                'invoice_items' => $document->invoice_items,
                'fiscalDocument' => null,
            ])
            ->setPaper('a4')
            ->output();
    }

    /**
     * Gonyeti as the letterhead of a license invoice / receipt. A copy, so the
     * tweaks never get saved: no "Powered By" strip on Gonyeti's own documents.
     */
    private function documentIssuer(): Company
    {
        $issuer = $this->issuer();
        $issuer = $issuer ? clone $issuer : new Company(['name' => 'Gonyeti TLS']);

        $issuer->show_branding = false;
        $issuer->invoice_title = $issuer->invoice_title ?: 'Invoice';
        $issuer->items_column = $issuer->items_column ?: 'Items';
        $issuer->units_column = $issuer->units_column ?: 'Qty';
        $issuer->price_column = $issuer->price_column ?: 'Price';
        $issuer->total_column = $issuer->total_column ?: 'Total';

        return $issuer;
    }

    /** Receipt for one payment, in the same classic styling as the invoice. */
    public function receiptPdf(SubscriptionPayment $payment): string
    {
        $payment->loadMissing(['invoice.company', 'invoice.currency']);
        $invoice = $payment->invoice;

        // Balance as it stood once this payment was in, not today's balance.
        $paidToDate = (float) $invoice->payments()
            ->where(fn ($q) => $q->where('date', '<', $payment->date->toDateString())
                ->orWhere(fn ($q2) => $q2->whereDate('date', $payment->date->toDateString())->where('id', '<=', $payment->id)))
            ->sum('amount');

        return app()->make('dompdf.wrapper')
            ->loadView('billing.receipt_pdf', [
                'payment' => $payment,
                'invoice' => $invoice,
                'client' => $invoice->company,
                'company' => $this->documentIssuer(),
                'receiptNumber' => $this->receiptNumber($payment),
                'balanceAfter' => round(max(0, $invoice->amount - $paidToDate), 2),
            ])
            ->setPaper('a4')
            ->output();
    }

    public function receiptNumber(SubscriptionPayment $payment): string
    {
        return 'GRCT-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT);
    }

    public function pdfFilename(SubscriptionInvoice $invoice): string
    {
        return 'Gonyeti-Invoice-' . $invoice->invoice_number . '.pdf';
    }

    /**
     * Deliver the invoice ($type 'invoice') or a payment reminder ('reminder')
     * to every recipient on each channel they are opted into. Every attempt is
     * logged in subscription_notifications.
     *
     * @return array{sent:int, failed:int}
     */
    public function send(SubscriptionInvoice $invoice, string $type = 'invoice'): array
    {
        $invoice->loadMissing(['company', 'currency']);
        $company = $invoice->company;
        $result = ['sent' => 0, 'failed' => 0];

        if (! $company) {
            return $result;
        }

        $pdf = $this->pdf($invoice);
        $filename = $this->pdfFilename($invoice);
        $issuer = $this->issuer();

        foreach ($this->recipients($company) as $contact) {
            if ($contact->send_email && $contact->email) {
                $ok = $this->attempt($invoice, $type, 'email', $contact->email, function () use ($invoice, $type, $contact, $pdf, $filename, $issuer) {
                    Mail::to($contact->email)->send(new SubscriptionInvoiceMail($invoice, $type, $contact->name, $pdf, $filename, $issuer));
                });
                $result[$ok ? 'sent' : 'failed']++;
            }

            if ($contact->send_whatsapp && $contact->phonenumber && $this->whatsapp->isConfigured()) {
                $ok = $this->attempt($invoice, $type, 'whatsapp', $contact->phonenumber, function () use ($invoice, $type, $contact, $pdf, $filename) {
                    $this->whatsapp->sendDocument(
                        $contact->phonenumber,
                        $pdf,
                        $filename,
                        $this->whatsappText($invoice, $type),
                        config($type === 'reminder' ? 'services.whatsapp.reminder_template' : 'services.whatsapp.invoice_template') ?: null,
                        [$invoice->company->name, $invoice->invoice_number, $invoice->money($invoice->balance), $invoice->due_date->format('d M Y')]
                    );
                });
                $result[$ok ? 'sent' : 'failed']++;
            }
        }

        if ($type === 'reminder') {
            if ($result['sent'] > 0) {
                $invoice->last_reminder_at = now();
                $invoice->reminder_count++;
            }
        } else {
            $invoice->send_attempts = min(255, $invoice->send_attempts + 1);
            if ($result['sent'] > 0) {
                $invoice->sent_at = $invoice->sent_at ?? now();
            }
        }
        $invoice->save();

        return $result;
    }

    private function attempt(SubscriptionInvoice $invoice, string $type, string $channel, string $recipient, callable $send): bool
    {
        $error = null;
        try {
            $send();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            Log::warning("Subscription billing: {$channel} {$type} for {$invoice->invoice_number} to {$recipient} failed: {$error}");
        }

        SubscriptionNotification::create([
            'subscription_invoice_id' => $invoice->id,
            'type' => $type,
            'channel' => $channel,
            'recipient' => $recipient,
            'status' => $error === null ? 'sent' : 'failed',
            'error' => $error === null ? null : mb_substr($error, 0, 1000),
        ]);

        return $error === null;
    }

    private function whatsappText(SubscriptionInvoice $invoice, string $type): string
    {
        $amount = $invoice->money($invoice->balance);
        $due = $invoice->due_date->format('d M Y');

        if ($type === 'reminder') {
            return $invoice->isOverdue()
                ? "Payment reminder: Gonyeti TLS invoice {$invoice->invoice_number} for {$amount} was due on {$due} and is still outstanding. Please arrange payment."
                : "Payment reminder: Gonyeti TLS invoice {$invoice->invoice_number} for {$amount} is due on {$due}.";
        }

        return "Gonyeti TLS invoice {$invoice->invoice_number} for {$invoice->company->name}: {$amount}, due {$due}.";
    }

    /**
     * Remind every client with an outstanding, already-delivered invoice whose
     * last contact was at least billing_reminder_days ago. Returns the number
     * of invoices reminded.
     */
    public function sendReminders(?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $reminded = 0;

        $invoices = SubscriptionInvoice::outstanding()
            ->whereNotNull('sent_at')
            ->whereHas('company', fn ($q) => $q->where('billing_enabled', true)->where('billing_reminder_days', '>', 0))
            ->with('company')
            ->get();

        foreach ($invoices as $invoice) {
            $last = ($invoice->last_reminder_at ?? $invoice->sent_at)->copy()->startOfDay();

            if ($last->diffInDays($today, false) < (int) $invoice->company->billing_reminder_days) {
                continue;
            }

            try {
                if ($this->send($invoice, 'reminder')['sent'] > 0) {
                    $reminded++;
                }
            } catch (\Throwable $e) {
                Log::warning("Subscription billing: reminder for {$invoice->invoice_number} failed: " . $e->getMessage());
            }
        }

        return $reminded;
    }

    // ------------------------------------------------------------------
    // Payments
    // ------------------------------------------------------------------

    /** @param array{date:string, amount:float|string, method?:?string, reference?:?string, notes?:?string} $data */
    public function recordPayment(SubscriptionInvoice $invoice, array $data, ?int $recordedBy = null): SubscriptionPayment
    {
        return DB::transaction(function () use ($invoice, $data, $recordedBy) {
            $invoice = SubscriptionInvoice::lockForUpdate()->findOrFail($invoice->id);
            $amount = round((float) $data['amount'], 2);

            if ($invoice->status === SubscriptionInvoice::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['pay_amount' => 'This invoice is cancelled.']);
            }
            if ($amount <= 0) {
                throw ValidationException::withMessages(['pay_amount' => 'Enter an amount greater than zero.']);
            }
            if ($amount > $invoice->balance + 0.001) {
                throw ValidationException::withMessages(['pay_amount' => 'The amount is more than the outstanding balance of ' . $invoice->money($invoice->balance) . '.']);
            }

            $payment = SubscriptionPayment::create([
                'company_id' => $invoice->company_id,
                'subscription_invoice_id' => $invoice->id,
                'date' => $data['date'],
                'amount' => $amount,
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $recordedBy,
            ]);

            $invoice->recalculate();

            return $payment;
        });
    }

    public function deletePayment(SubscriptionPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $payment->delete();
            $invoice?->recalculate();
        });
    }

    /** Cancel an invoice nobody has paid against (reminders stop; the month stays closed). */
    public function cancel(SubscriptionInvoice $invoice): void
    {
        if ($invoice->payments()->exists()) {
            throw ValidationException::withMessages(['invoice' => 'Remove the payments on this invoice before cancelling it.']);
        }

        $invoice->status = SubscriptionInvoice::STATUS_CANCELLED;
        $invoice->balance = 0;
        $invoice->save();
    }
}
