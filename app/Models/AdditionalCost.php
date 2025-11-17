<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdditionalCost extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function cost_item(){
        return $this->belongsTo('App\Models\CostItem');
    }
}
