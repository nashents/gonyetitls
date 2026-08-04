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
    public function units_of_measure(){
        return $this->belongsTo('App\Models\UnitsOfMeasure');
    }
    public function transfer_items(){
        return $this->hasMany('App\Models\TransferItem');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function ticket_requests(){
        return $this->hasMany('App\Models\TicketRequest');
    }
      public function movements(){
        return $this->hasMany('App\Models\Movement');
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
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }

    /** Sage Intacct link (entity_type product_item) for the sync badge/status. */
    public function sageMapping(){
        return $this->hasOne(\App\Models\IntegrationMapping::class, 'local_id')
            ->where('entity_type', 'product_item');
    }

    protected $fillable = [
        'user_id',
        'product_number',
        'category_id',
        'unit_of_measure',
        'category_value_id',
        'brand_id',
        'name',
        'type',
        'buy',
        'price',
        'sell_price',
        'sell',
        'department',
        'identification_number',
        'status',
    ];
}
