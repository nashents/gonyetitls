<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAuditTemplate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function sections(){
        return $this->hasMany('App\Models\SheqAuditSection')->orderBy('sort_order','asc');
    }
    public function items(){
        return $this->hasManyThrough('App\Models\SheqAuditItem','App\Models\SheqAuditSection');
    }
    public function sheq_audits(){
        return $this->hasMany('App\Models\SheqAudit');
    }
    public function possibleTotal(){
        return $this->items->sum('possible_mark');
    }
}
