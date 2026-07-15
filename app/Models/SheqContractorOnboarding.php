<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqContractorOnboarding extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function contractorable(){
        return $this->morphTo();
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }
    public function isInductionExpired(){
        return $this->induction_expiry && \Carbon\Carbon::parse($this->induction_expiry)->isPast();
    }
    public function contractorName(){
        return $this->contractorable->name ?? '-';
    }
}
