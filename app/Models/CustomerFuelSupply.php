<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Fuel a customer supplied in kind as part-settlement of their trip - see
 * the create_customer_fuel_supplies_table migration and
 * CustomerFuelSupplyService for the accounting.
 */
class CustomerFuelSupply extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id',
        'user_id',
        'supply_number',
        'customer_id',
        'currency_id',
        'exchange_rate',
        'fuel_id',
        'top_up_id',
        'trip_expense_id',
        'trip_id',
        'container_id',
        'purchase_type',
        'account_id',
        'date',
        'quantity',
        'unit_price',
        'amount',
        'description',
    ];

    protected $casts = [
        'date'          => 'date',
        'amount'        => 'decimal:2',
        'quantity'      => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function fuel()
    {
        return $this->belongsTo(Fuel::class);
    }

    public function top_up()
    {
        return $this->belongsTo(TopUp::class);
    }

    public function trip_expense()
    {
        return $this->belongsTo(TripExpense::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice_payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function journal_entry()
    {
        return $this->hasOne(JournalEntry::class)
            ->where('status', '!=', 'reversed')
            ->where(fn ($q) => $q->whereNull('reference')->orWhere('reference', 'not like', 'REV-%'))
            ->latestOfMany('id');
    }

    public function allocatedAmount(): float
    {
        return round((float) $this->invoice_payments()->sum(\DB::raw('COALESCE(amount+0,0)')), 2);
    }

    public function unallocatedAmount(): float
    {
        return round(max(0, (float) $this->amount - $this->allocatedAmount()), 2);
    }

    public function getStatusAttribute(): string
    {
        $allocated = $this->allocatedAmount();

        if ($allocated <= 0) {
            return 'Unallocated';
        }

        return $allocated + 0.005 >= (float) $this->amount ? 'Allocated' : 'Partial';
    }
}
