<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispatch extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
      public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function branch(){
        return $this->belongsTo('App\Models\Branch');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
   
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function dispatch_items(){
        return $this->hasMany('App\Models\DispatchItem');
    }
    public function ticket_inventories(){
        return $this->hasMany('App\Models\TicketInventory');
    }

    protected $casts = [
        'expand'      => 'boolean',
    ];


}
