<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAuditResponse extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function audit(){
        return $this->belongsTo('App\Models\SheqAudit','sheq_audit_id');
    }
    public function item(){
        return $this->belongsTo('App\Models\SheqAuditItem','sheq_audit_item_id');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }
}
