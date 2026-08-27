<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function companies(){
        return $this->belongsToMany('App\Models\Company', 'company_company_type');
    }

    protected $fillable = [
        'name',
        'description',
        'is_locked',
    ];
}
