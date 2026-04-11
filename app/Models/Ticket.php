<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
      public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
     public function work_dones(){
        return $this->hasMany('App\Models\WorkDone');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function employees(){
        return $this->belongsToMany('App\Models\Employee');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }

    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function ticket_images(){
        return $this->hasMany('App\Models\TicketImage');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function ticket_inventories(){
        return $this->hasMany('App\Models\TicketInventory');
    }
    public function ticket_expenses(){
        return $this->hasMany('App\Models\TicketExpense');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
    
    public function dispatches(){
        return $this->hasMany('App\Models\Dispatch');
    }
    public function inventory_dispatches(){
        return $this->hasMany('App\Models\InventoryDispatch');
    }
    public function tyre_dispatches(){
        return $this->hasMany('App\Models\TyreDispatch');
    }
    public function tyre_requisitions(){
        return $this->hasMany('App\Models\TyreRequisition');
    }
    public function ticket_requests(){
        return $this->hasMany('App\Models\TicketRequest');
    }
    public function inventory_requisitions(){
        return $this->hasMany('App\Models\InventoryRequisition');
    }
    public function inspection(){
        return $this->belongsTo('App\Models\Inspection');
    }
    public function job_type(){
        return $this->belongsTo('App\Models\JobType');
    }
    public function service_type(){
        return $this->belongsTo('App\Models\ServiceType');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
  
}
