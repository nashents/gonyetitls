<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoicePayment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    
    public function payment(){
        return $this->belongsTo('App\Models\Payment');
    }

    public function customer_fuel_supply(){
        return $this->belongsTo('App\Models\CustomerFuelSupply');
    }

    public function debtor_journal(){
        return $this->belongsTo('App\Models\DebtorJournal');
    }

    /** Debit debtors journal (e.g. opening balance) this deposit allocation settles - instead of an invoice. */
    public function settled_debtor_journal(){
        return $this->belongsTo('App\Models\DebtorJournal', 'settled_debtor_journal_id');
    }

    public function items(){
        return $this->hasMany('App\Models\InvoicePaymentItem');
    }
}
