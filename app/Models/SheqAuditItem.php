<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAuditItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function section(){
        return $this->belongsTo('App\Models\SheqAuditSection','sheq_audit_section_id');
    }
    public function responses(){
        return $this->hasMany('App\Models\SheqAuditResponse');
    }
}
