<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function expenses(){
        return $this->hasMany('App\Models\Expense');
    }
    public function ticket_expenses(){
        return $this->hasMany('App\Models\TicketExpense');
    }
    public function trip_expenses(){
        return $this->hasMany('App\Models\TripExpense');
    }
    public function purchase_products(){
        return $this->hasMany('App\Models\PurchaseProduct');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function route_expenses(){
        return $this->hasMany('App\Models\RouteExpense');
    }

    protected $fillable =[
        'user_id',
        'name',
        'is_locked',
    ];
}
