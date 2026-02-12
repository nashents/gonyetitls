<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WasteType extends Model
{
    use HasFactory, SoftDeletes;

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
