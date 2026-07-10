<?php

namespace App\Models;

use App\Models\Horse;
use App\Models\Trailer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class Cluster extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function horse(){
         return $this->belongsTo(Horse::class);
    }
    
    public function trailers(){
         return $this->belongsToMany(Trailer::class)->withPivot(['position','attached_at'])->withTimestamps();
    }
}
