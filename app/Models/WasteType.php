<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class WasteType extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function waste_collection_items(){
        return $this->hasMany('App\Models\WasteCollectionItem');
    }
    public function waste_disposal_items(){
        return $this->hasMany('App\Models\WasteDisposalItem');
    }

     protected $fillable = [
        'name',
        'category',
        'generation_area',
        'composition',
        'impact',
        'control_methods',
        'status',
    ];
}
