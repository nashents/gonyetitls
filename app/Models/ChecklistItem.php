<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function checklist_results(){
        return $this->hasMany('App\Models\ChecklistResult');
    }
    public function category_checklists(){
        return $this->hasMany('App\Models\CategoryChecklist');
    }
    public function checklist_sub_category(){
        return $this->belongsTo('App\Models\ChecklistSubCategory');
    }

    protected $fillable = [
        'checklist_category_id',
        'checklist_sub_category_id',
        'name',
        'notes',
        'user_id',
    ];
}
