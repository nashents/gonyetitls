<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function sale_items(){
        return $this->hasMany('App\Models\SaleItem');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    public function bill_expenses(){
        return $this->hasMany('App\Models\BillExpense');
    }
    public function ticket_expenses(){
        return $this->hasMany('App\Models\TicketExpense');
    }
    public function tyres(){
        return $this->hasMany('App\Models\Tyre');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
    public function category_value(){
        return $this->belongsTo('App\Models\CategoryValue');
    }
    public function attributes(){
        return $this->belongsToMany('App\Models\Attribute');
    }
    public function attribute_values(){
        return $this->belongsToMany('App\Models\AttributeValue');
    }
    public function brand(){
        return $this->belongsTo('App\Models\Brand');
    }

    public function order_products(){
        return $this->hasMany('App\Models\OrderProduct');
    }
    public function purchase_products(){
        return $this->hasMany('App\Models\PurchaseProduct');
    }
    public function invoice_items(){
        return $this->hasMany('App\Models\InvoiceItem');
    }
    public function product_attributes(){
        return $this->hasMany('App\Models\ProductAttribute');
    }
    public function stocks(){
        return $this->hasMany('App\Models\Stock');
    }
    public function assets(){
        return $this->hasMany('App\Models\Asset');
    }
    public function inventories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    public function vehicle_make(){
        return $this->belongsTo('App\Models\VehicleMake');
    }

    protected $fillable = [
        'user_id',
        'product_number',
        'category_id',
        'brand_id',
        'name',
        'department',
        'status',
    ];
}
