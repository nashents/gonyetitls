<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryChecklist extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function checklist_item(){
        return $this->belongsTo('App\Models\ChecklistItem');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function checklist_category(){
        return $this->belongsTo('App\Models\ChecklistCategory');
    }
    public function checklist_sub_category(){
        return $this->belongsTo('App\Models\ChecklistSubCategory');
    }
}
