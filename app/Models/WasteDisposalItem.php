<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteDisposalItem extends Model
{
    use HasFactory, SoftDeletes;

    public function waste_type(){
        return $this->belongsTo('App\Models\WasteType');
    }
    public function waste_disposal(){
        return $this->belongsTo('App\Models\WasteDisposal');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
}
