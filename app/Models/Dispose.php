<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispose extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tyre_id',
        'comments',
        'date',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
}
