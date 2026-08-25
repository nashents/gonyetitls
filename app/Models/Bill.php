<?php

namespace App\Models;

use App\Contracts\EditAuthorizable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bill extends Model implements Auditable, EditAuthorizable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function edit_authorization_requests()
    {
        return $this->morphMany(EditAuthorizationRequest::class, 'editable');
    }

    public function editAuthModule(): string
    {
        return 'bills';
    }

    public function editAuthOwnerId(): ?int
    {
        return $this->user_id;
    }

    public function editAuthLabel(): string
    {
        return 'Bill '.$this->bill_number;
    }

    protected static ?int $vatAccountIdCache = null;

    /**
     * Whether the posted journal entry's per-account debit breakdown still
     * matches the bill_expenses' current account allocation. Catches the
     * case LedgerResyncService::isUnchanged() deliberately doesn't - a
     * bill_expense.account_id reassigned to a different account with the
     * same subtotal (so the bill's total, and therefore the Accounts
     * Payable control line, never changes) leaves the journal entry's debit
     * lines silently wrong even after a non-forced resync.
     */
    public function getIsLedgerOutOfSyncAttribute(): bool
    {
        $entry = $this->journal_entry;

        if (! $entry) {
            return false;
        }

        static::$vatAccountIdCache ??= Account::where('name', 'Value Added Tax')->value('id');
        $vatAccountId = static::$vatAccountIdCache;

        $expenseTotals = $this->bill_expenses
            ->whereNotNull('account_id')
            ->groupBy('account_id')
            ->map(fn ($rows) => round((float) $rows->sum('subtotal'), 2));

        $lineTotals = $entry->journal_entry_lines
            ->filter(fn ($line) => (float) $line->debit > 0 && $line->account_id && $line->account_id != $vatAccountId)
            ->groupBy('account_id')
            ->map(fn ($rows) => round((float) $rows->sum('debit'), 2));

        if ($expenseTotals->keys()->diff($lineTotals->keys())->isNotEmpty()
            || $lineTotals->keys()->diff($expenseTotals->keys())->isNotEmpty()) {
            return true;
        }

        foreach ($expenseTotals as $accountId => $amount) {
            if (abs($lineTotals[$accountId] - $amount) > 0.01) {
                return true;
            }
        }

        return false;
    }

    public function bill_expenses(){
        return $this->hasMany('App\Models\BillExpense');
    }
    public function journal_entry(){
        return $this->hasOne(JournalEntry::class)->where('status', '!=', 'reversed')->latestOfMany('id');
    }
    public function dispatch(){
        return $this->belongsTo('App\Models\Dispatch');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
     public function bill_payments(){
        return $this->hasMany('App\Models\BillPayment');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function requisition(){
        return $this->belongsTo('App\Models\Requisition');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function deleted_by(){
        return $this->belongsTo('App\Models\User', 'deleted_by_id');
    }
    public function retread(){
        return $this->belongsTo('App\Models\Retread');
    }
    public function fuel(){
        return $this->belongsTo('App\Models\Fuel');
    }
    public function workshop_service(){
        return $this->belongsTo('App\Models\WorkshopService');
    }
    public function ticket_expense(){
        return $this->BelongsTo('App\Models\TicketExpense');
    }
    public function inventory(){
        return $this->BelongsTo('App\Models\Inventory');
    }
    public function asset(){
        return $this->BelongsTo('App\Models\Asset');
    }
    public function tyre(){
        return $this->BelongsTo('App\Models\Tyre');
    }
    public function ticket(){
        return $this->BelongsTo('App\Models\Ticket');
    }
    public function ticket_inventory(){
        return $this->belongsTo('App\Models\TicketInventory');
    }
    public function top_up(){
        return $this->belongsTo('App\Models\TopUp');
    }
    public function goods_received(){
        return $this->belongsTo('App\Models\GoodsReceived');
    }
    public function container(){
        return $this->belongsTo('App\Models\Container');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function trip_expense(){
        return $this->belongsTo('App\Models\TripExpense');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
}
