<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class WasteCollectionItem extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function waste_collection(){
        return $this->belongsTo('App\Models\WasteCollection');
    }
    public function waste_type(){
        return $this->belongsTo('App\Models\WasteType');
    }
    
     public function collectedBy()
    {
        return $this->belongsTo(Employee::class, 'collected_by_id');
    }


}
