<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouteExpense extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function route(){
        return $this->belongsTo('App\Models\Route');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
}
