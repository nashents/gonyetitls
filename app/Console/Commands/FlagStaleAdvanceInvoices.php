<?php

namespace App\Console\Commands;

use App\Mail\StaleAdvanceInvoicesMail;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class FlagStaleAdvanceInvoices extends Command
{
    protected $signature = 'trips:flag-stale-advance-invoices';
    protected $description = 'Notify Finance about approved Advance invoices whose revenue is still deferred in Customer Advances past the configured aging threshold.';

    public function handle(): int
    {
        $agingDays = (int) config('accounting.advance_aging_days', 30);

        $staleInvoices = Invoice::where('invoice_type', 'advance')
            ->where('authorization', 'approved')
            ->whereDoesntHave('journal_entries', fn ($q) => $q->where('reference', 'like', 'RECLASS-%'))
            ->where('date', '<=', now()->subDays($agingDays))
            ->with(['customer', 'currency', 'company'])
            ->get();

        if ($staleInvoices->isEmpty()) {
            $this->info('No stale advance invoices found.');
            return self::SUCCESS;
        }

        $byCompany = $staleInvoices->groupBy('company_id');

        foreach ($byCompany as $companyId => $invoices) {
            $company = $companyId ? Company::find($companyId) : null;

            $recipients = User::whereHas('employee.departments', fn ($q) => $q->where('name', 'Finance'))
                ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('company_id', $companyId)))
                ->pluck('email')
                ->filter()
                ->unique();

            if ($recipients->isEmpty()) {
                $this->warn("No Finance recipients found for company #{$companyId} — {$invoices->count()} stale invoice(s) unreported.");
                continue;
            }

            Mail::to($recipients->all())->send(new StaleAdvanceInvoicesMail($invoices, $company, $agingDays));
        }

        $this->info("Flagged {$staleInvoices->count()} stale advance invoice(s) across {$byCompany->count()} company/companies.");

        return self::SUCCESS;
    }
}
