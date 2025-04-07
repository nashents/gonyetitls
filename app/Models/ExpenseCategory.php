<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseCategory extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function expenses(){
        return $this->hasMany('App\Models\Expense');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function account_type(){
        return $this->belongsTo('App\Models\AccountType');
    }

    protected $fillable = [
        'user_id',
        'account_type_id',
        'name',
        'group',
        'description',
    ];
}
