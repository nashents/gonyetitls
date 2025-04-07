<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function trip_expenses(){
        return $this->hasOne('App\Models\TripExpense');
    }
    
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function workshop_services(){
        return $this->hasMany('App\Models\WorkshopService');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function ticket_expenses(){
        return $this->hasMany('App\Models\TicketExpense');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function bill_expenses(){
        return $this->hasMany('App\Models\BillExpense');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
   
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    protected $fillable = [
        'user_id',
        'account_id',
        'name',
        'frequency',
        'description',
        'type',
        'status',
    ];
}
