<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubModule extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

     public function module(){
        return $this->belongsTo('App\Models\Module');
    }
   

    protected $casts = [
        'route_params' => 'array',
        'visibility'   => 'array',
    ];

    protected $fillable = [
        'module_id','slug','name','icon','route_name','route_params','url',
        'sort_order','is_active','badge_key','visibility'
    ];
}
