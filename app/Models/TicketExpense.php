<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketExpense extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }

    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }

    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }

}
