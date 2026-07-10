<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WasteDisposal extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }

    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }

    public function waste_disposal_items(){
        return $this->hasMany('App\Models\WasteDisposalItem');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
}
