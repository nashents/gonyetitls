<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqPpeIssue extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function issued_by(){
        return $this->belongsTo('App\Models\User','issued_by_id');
    }
    public function isReplacementDue(){
        return $this->next_replacement_date && \Carbon\Carbon::parse($this->next_replacement_date)->isPast();
    }
}
