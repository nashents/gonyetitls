<?php

namespace App\Models;

use App\Models\Horse;
use App\Models\Trailer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cluster extends Model
{
    use HasFactory, SoftDeletes;

    public function horse(){
         return $this->belongsTo(Horse::class);
    }
    
    public function trailers(){
         return $this->belongsToMany(Trailer::class)->withPivot(['position','attached_at'])->withTimestamps();
    }
}
