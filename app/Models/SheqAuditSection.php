<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAuditSection extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function template(){
        return $this->belongsTo('App\Models\SheqAuditTemplate','sheq_audit_template_id');
    }
    public function items(){
        return $this->hasMany('App\Models\SheqAuditItem')->orderBy('sort_order','asc');
    }
    public function possibleTotal(){
        return $this->items->sum('possible_mark');
    }
}
