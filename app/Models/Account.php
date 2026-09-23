<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    public function account_type(){
        return $this->belongsTo('App\Models\AccountType');
    }
    public function account_type_group(){
        return $this->belongsTo('App\Models\AccountTypeGroup');
    }
    public function taxes(){
        return $this->hasMany('App\Models\Tax');
    }
    public function retreads(){
        return $this->hasMany('App\Models\Retread');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
    public function loans(){
        return $this->hasMany('App\Models\Loan');
    }
    public function sale_items(){
        return $this->hasMany('App\Models\SaleItem');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function bill_expenses(){
        return $this->hasMany('App\Models\BillExpense');
    }
    public function workshop_services(){
        return $this->hasMany('App\Models\WorkshopService');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function expenses(){
        return $this->hasMany('App\Models\Expense');
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function bank_account(){
        return $this->belongsTo('App\Models\BankAccount');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    public function ticket_expenses(){
        return $this->hasMany('App\Models\TicketExpense');
    }
    public function requisitions(){
        return $this->hasMany('App\Models\Requisition');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }

    protected $fillable=[
        'bank_account_id',
        'account_type_id',
        'currency_id',
        'account_number',
        'name',
        'code',
        'description',
        'hs_code',
        'is_locked',
        'status',
    ];

    /** Active accounts only — for pickers where nothing is already selected (e.g. Create forms). */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Active accounts, plus a specific inactive one kept visible — for Edit forms, so a bill/
     * invoice/etc already pointing at a since-deactivated account doesn't silently lose it
     * from the dropdown (and doesn't get its account_id blanked out on next save).
     */
    public function scopeSelectable($query, $keepId = null)
    {
        return $query->where(function ($q) use ($keepId) {
            $q->where('status', 1);
            if (!empty($keepId)) {
                $q->orWhere('id', $keepId);
            }
        });
    }
}
