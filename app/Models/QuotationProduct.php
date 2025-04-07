<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationProduct extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function loading_point(){
        return $this->belongsTo('App\Models\LoadingPoint');
    }
    public function offloading_point(){
        return $this->belongsTo('App\Models\OffloadingPoint');
    }
}
