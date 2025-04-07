<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function expense_categories(){
        return $this->hasMany('App\Models\ExpenseCategories');
    }
    public function accounts(){
        return $this->hasMany('App\Models\Account');
    }
    public function retreads(){
        return $this->hasMany('App\Models\Retread');
    }
    public function account_type_group(){
        return $this->belongsTo('App\Models\AccountTypeGroup');
    }

    protected $fillable=[
        'user_id',
        'name',
        'account_type_group_id',
    ];
}
