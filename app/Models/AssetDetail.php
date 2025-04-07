<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function asset_dispatch(){
        return $this->belongsTo('App\Models\AssetDispatch');
    }


}
