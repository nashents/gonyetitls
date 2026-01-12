<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function module_group(){
        return $this->belongsTo('App\Models\ModuleGroup');
    }
    public function sub_modules(){
        return $this->hasMany('App\Models\SubModule');
    }
    
    protected $casts = [
        'route_params' => 'array',
        'visibility'   => 'array',
    ];

    protected $fillable = [
        'module_group_id','slug','name','icon','route_name','route_params','url',
        'sort_order','is_active','badge_key','visibility'
    ];

}
