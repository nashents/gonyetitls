<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'accrual_balance' => 'decimal:2', // Ensures it's treated as a decimal
    ];

    public function invoice_products(){
        return $this->hasMany('App\Models\InvoiceProduct');
    }
    public function journal_entry(){
        return $this->hasOne(JournalEntry::class)->where('status', '!=', 'reversed')->latestOfMany('id');
    }
    public function journal_entries(){
        return $this->hasMany(JournalEntry::class);
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function additional_costs(){
        return $this->hasMany('App\Models\AdditionalCost');
    }
    public function discount(){
        return $this->hasOne('App\Models\Discount');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function deleted_by(){
        return $this->belongsTo('App\Models\User', 'deleted_by_id');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }

    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function invoice_payments(){
        return $this->hasMany('App\Models\InvoicePayment');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }

    /** Sage Intacct link (entity_type sales_invoice) for the sync badge/status. */
    public function sageMapping(){
        return $this->hasOne(\App\Models\IntegrationMapping::class, 'local_id')
            ->where('entity_type', 'sales_invoice');
    }

    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bank_accounts(){
        return $this->belongsToMany('App\Models\BankAccount');
    }
    // public function trip(){
    //     return $this->belongsTo('App\Models\Trip');
    // }
    
    public function credit_notes(){
        return $this->hasMany('App\Models\CreditNote');
    }

    /**
     * Total of the approved credit notes raised against this invoice.
     */
    public function creditedAmount(): float
    {
        return round((float) $this->credit_notes()
            ->where('authorization', 'approved')
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(total+0,0)')), 2);
    }

    /**
     * Recompute balance/status from the invoice total less everything that
     * settles it: payments, customer-supplied fuel, debtors journal credits
     * and approved credit notes. Saves the invoice.
     */
    public function recalculateBalance(): self
    {
        $directPayments = (float) $this->payments()->whereNotNull('amount')->where('amount', '!=', '')->sum('amount');

        // Bulk payments (payment.invoice_id null) settle invoices only through
        // invoice_payments rows. Rows whose payment is already linked straight
        // to this invoice duplicate $directPayments, so they're skipped.
        $bulkAllocations = (float) InvoicePayment::where('invoice_id', $this->id)
            ->where(fn ($q) => $q->whereNull('source')->orWhereNotIn('source', [
                \App\Services\Accounting\CustomerFuelSupplyService::SOURCE,
                \App\Services\Accounting\DebtorJournalService::SOURCE,
            ]))
            ->whereHas('payment', fn ($q) => $q->where(fn ($q) => $q->whereNull('invoice_id')->orWhere('invoice_id', '!=', $this->id)))
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(amount+0,0)'));

        $settled = $directPayments + $bulkAllocations
            + app(\App\Services\Accounting\CustomerFuelSupplyService::class)->allocatedToInvoice($this)
            + app(\App\Services\Accounting\DebtorJournalService::class)->allocatedToInvoice($this)
            + $this->creditedAmount();

        $total = round((float) $this->total, 2);
        $balance = round(max(0, $total - $settled), 2);

        $this->balance = $balance;
        $this->status = $balance <= 0 ? 'Paid' : ($balance < $total ? 'Partial' : 'Unpaid');
        $this->invoice_status = $balance <= 0 ? 0 : 1;
        $this->save();

        return $this;
    }

    /**
     * Why a credit note for $amount can't be raised/approved against this
     * invoice, or null if it can. The note may not exceed the outstanding
     * balance, less other pending credit notes when $blockOnPending is true
     * (creating/editing) - approval passes false since only approved notes
     * have actually reduced the balance.
     * Pass $ignoreCreditNoteId when checking an existing credit note: if it
     * is already approved on this invoice its own total is added back.
     */
    public function creditNoteBlockReason($ignoreCreditNoteId = null, bool $blockOnPending = true, $amount = null): ?string
    {
        $remaining = (float) $this->balance;

        if ($ignoreCreditNoteId) {
            $self = $this->credit_notes()->where('id', $ignoreCreditNoteId)->where('authorization', 'approved')->first();
            if ($self) {
                $remaining += (float) $self->total;
            }
        }

        $pendingTotal = 0;
        if ($blockOnPending) {
            $pendingTotal = (float) $this->credit_notes()
                ->where('authorization', 'pending')
                ->when($ignoreCreditNoteId, fn ($q) => $q->where('id', '!=', $ignoreCreditNoteId))
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(total+0,0)'));
            $remaining -= $pendingTotal;
        }

        $remaining = round($remaining, 2);

        if ($remaining <= 0) {
            return $pendingTotal > 0
                ? "Invoice {$this->invoice_number}'s balance is already covered by pending credit notes. Approve, reject or edit those instead."
                : "Invoice {$this->invoice_number} has no outstanding balance to credit.";
        }

        if ($amount !== null && round((float) $amount, 2) > $remaining) {
            return "Credit note total " . number_format((float) $amount, 2) . " exceeds the " . number_format($remaining, 2)
                . " that can still be credited on invoice {$this->invoice_number}"
                . ($pendingTotal > 0 ? " (after other pending credit notes)." : ".");
        }

        return null;
    }

    public function invoice_trips(){
        return $this->hasMany('App\Models\InvoiceTrip');
    }

    protected $fillable =[
        'user_id',
        'customer_id',
        'currency_id',
        'invoice_number',
        'trip_id',
        'vat',
        'total',
        'subtotal',
        'date',
        'expiry',
        'memo',
        'footer',
        'subheading',
        'invoice_type',
        'advance_payment_type',
    ];

    public function getIsAdvanceInvoiceAttribute(): bool
    {
        return $this->invoice_type === 'advance';
    }

    public function getRevenueAccountLabelAttribute(): string
    {
        if ($this->invoice_type !== 'advance') {
            return 'Sales';
        }

        $entries = $this->relationLoaded('journal_entries')
            ? $this->journal_entries
            : $this->journal_entries()->get();

        $reclassed = $entries->contains(fn ($entry) => str_starts_with((string) $entry->reference, 'RECLASS-'));

        return $reclassed ? 'Sales (Recognized)' : 'Customer Advances (Deferred)';
    }
}
