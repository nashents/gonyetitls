<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequisitionItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function requisition(){
        return $this->belongsTo('App\Models\Requisition');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function expense_category(){
        return $this->belongsTo('App\Models\ExpenseCategory');
    }
}
