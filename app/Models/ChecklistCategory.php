<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistCategory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function category_checklists(){
        return $this->hasMany('App\Models\CategoryChecklist');
    }
    public function checklists(){
        return $this->hasMany('App\Models\Checklist');
    }
    
    protected $fillable = [
        'name',
        'user_id',
        'is_locked',
    ];

    
}
