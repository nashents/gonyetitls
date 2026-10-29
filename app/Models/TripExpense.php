<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripExpense extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function payment_method(){
        return $this->belongsTo('App\Models\PaymentMethod');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }

    /** Trip sheet visibility is set once on the Expense; allowances always show. */
    public function showsOnTripSheet(): bool
    {
        return $this->expense ? (bool) ($this->expense->visible_on_trip_sheet ?? true) : true;
    }
    public function allowance(){
        return $this->belongsTo('App\Models\Allowance');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function fuel(){
        return $this->belongsTo('App\Models\Fuel');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function customer_fuel_supply(){
        return $this->hasOne('App\Models\CustomerFuelSupply');
    }

    public function getCanDeleteAttribute()
    {
        if (auth()->check() && auth()->user()->canOverrideTripLocks()) {
            return true;
        }

        if ($this->user_id != auth()->id()) {
            return false;
        }

        return !$this->bill || !$this->bill->payments()->exists();
    }

    public function getCanEditAttribute()
    {
        if (auth()->check() && auth()->user()->canOverrideTripLocks()) {
            return true;
        }

        return $this->can_delete && !$this->fuel;
    }

    public function getBillPaidStatusAttribute()
    {
        return optional($this->resolvedBill())->status;
    }

    /**
     * The supplier bill behind this line: its own (bills.trip_expense_id) or,
     * for a fuel line, the fuel order's bill (older fuel bills were never
     * linked to the trip expense row, so the status showed "No Bill").
     */
    public function resolvedBill(): ?Bill
    {
        if ($this->bill) {
            return $this->bill;
        }

        return $this->fuel_id ? Bill::where('fuel_id', $this->fuel_id)->first() : null;
    }

    /**
     * Bill status for the trip Expenses tab: [label, badge class, tooltip].
     * Where there's no bill, says why instead of a bare "No Bill".
     */
    public function billStatusInfo(): array
    {
        if ($bill = $this->resolvedBill()) {
            if ($this->fuel_id && ! $bill->to_be_paid) {
                return ['From bulk stock', 'bg-info', "Bill {$bill->bill_number}: drawn from fuel already bought for the tank - nothing to pay the station"];
            }

            return match ($bill->status) {
                'Paid'    => ['Paid', 'bg-success', "Bill {$bill->bill_number}"],
                'Partial' => ['Partial', 'bg-warning', "Bill {$bill->bill_number}"],
                default   => ['Unpaid', 'bg-danger', "Bill {$bill->bill_number}"],
            };
        }

        if ($this->fuel_id) {
            $fuel = Fuel::withTrashed()->find($this->fuel_id);
            $order = optional($fuel)->order_number ?? "#{$this->fuel_id}";

            if (! $fuel || $fuel->trashed()) {
                return ['Fuel order deleted', 'bg-danger', "Fuel order {$order} was deleted - remove this line from the trip"];
            }
            if ($fuel->supplied_by_customer || $this->supplied_by_customer) {
                return ['Customer supplied', 'bg-info', "Fuel order {$order} was supplied by the customer - no supplier bill, charged to the customer instead"];
            }
            if ($fuel->authorization !== 'approved') {
                return ['Fuel order ' . ($fuel->authorization ?: 'pending'), 'bg-secondary', "The bill is raised when fuel order {$order} is approved"];
            }
            if (Bill::onlyTrashed()->where('fuel_id', $fuel->id)->exists()) {
                return ['Bill deleted', 'bg-danger', "Fuel order {$order} is approved but its bill was deleted - no cost is recorded. Restore the bill (Bills > Deleted) or reject the fuel order"];
            }

            return ['No bill', 'bg-danger', "Fuel order {$order} is approved but has no bill"];
        }

        if ($this->supplied_by_customer) {
            return ['Customer funded', 'bg-info', 'Paid by the customer - no supplier bill'];
        }
        if ($this->funded_by_transporter) {
            return ['Transporter funded', 'bg-info', 'Paid by the transporter - recorded only, no bill'];
        }
        if ($this->expense && ! $this->expense->is_locked && ! ($this->expense->billable ?? true)) {
            return ['Not billable', 'bg-secondary', 'Expense is set as non-billable - trip cost only, no supplier bill'];
        }
        if (optional($this->trip)->authorization !== 'approved') {
            return ['Awaiting trip approval', 'bg-secondary', 'The bill is raised when the trip is approved'];
        }

        return ['No bill', 'bg-danger', 'No supplier bill found for this expense'];
    }

    protected $fillable = [
        'user_id',
        'currency_id',
        'vendor_id',
        'expense_id',
        'trip_id',
        'quantity',
        'amount',
        'payment_method_id',
        'category',
        'supplied_by_customer',
        'funded_by_transporter',
        'customer_id',
        'visible_on_trip_sheet',
        'date',
    ];

    protected $casts = [
        'visible_on_trip_sheet' => 'boolean',
        'supplied_by_customer' => 'boolean',
        'funded_by_transporter' => 'boolean',
    ];
}
