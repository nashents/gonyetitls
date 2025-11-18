<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistResult extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function checklist_item(){
        return $this->belongsTo('App\Models\ChecklistItem');
    }
    public function category_checklist(){
        return $this->belongsTo('App\Models\CategoryChecklist');
    }
    public function checklist(){
        return $this->belongsTo('App\Models\Checklist');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function tyre_assignment(){
        return $this->belongsTo('App\Models\TyreAssignment');
    }

    protected $fillable = [
        'checklist_id',
        'checklist_item_id',
        'category_checklist_id',
        'status',
        'comments',
        'tread_depth_mm',
        'tyre_assignment_id',
        'pressure_psi',
        'valve_ok',
        'sidewall_damage',
        'wear_pattern',
        'rim_condition',
        'wheel_nuts_torqued',
        'axle_match',
        'action_required',
        'rating',
        'notes',
    ];

}
