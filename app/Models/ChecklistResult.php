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
    public function checklist(){
        return $this->belongsTo('App\Models\Checklist');
    }
}
