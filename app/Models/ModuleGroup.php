<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleGroup extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function modules(){
        return $this->hasMany('App\Models\Module');
    }

        protected $casts = [
        'visibility' => 'array',
        'is_active'  => 'boolean',
    ];

      protected $fillable = [
        'slug','name','icon',
        'sort_order','is_active','badge_key','visibility'
    ];

}
