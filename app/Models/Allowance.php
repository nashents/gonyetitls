<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allowance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function allowance_drivers(){
        return $this->hasMany('App\Models\AllowanceDriver');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function salary_items(){
        return $this->hasMany('App\Models\SalaryItem');
    }
    public function trip_expenses(){
        return $this->hasMany('App\Models\TripExpense');
    }
    public function bill_expenses(){
        return $this->hasMany('App\Models\BillExpense');
    }
}
