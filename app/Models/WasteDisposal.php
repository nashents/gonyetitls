<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteDisposal extends Model
{
    use HasFactory, SoftDeletes;

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
}
