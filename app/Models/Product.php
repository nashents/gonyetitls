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

    /** Keep the linked Expense's name in step when the product is renamed. */
    protected static function booted()
    {
        static::updated(function ($product) {
            if ($product->wasChanged('name')) {
                $expense = \App\Models\Expense::where('product_id', $product->id)->first();
                if ($expense && $expense->name !== $product->name) {
                    $expense->name = $product->name;
                    $expense->saveQuietly(); // no event → no sync loop
                }
            }
        });
    }

    /** The expense (GL) account this product/service is bought against. */
    public function expense_account(){
        return $this->belongsTo('App\Models\Account', 'expense_account_id');
    }

    /** The expense linked to this product (non-inventory billable items). */
    public function expense(){
        return $this->hasOne('App\Models\Expense');
    }
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
    /** Default store (prefilled on PO lines / receipts). Always also one of stores(). */
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }

    /** Every store this product may be kept in (product_store pivot). */
    public function stores(){
        return $this->belongsToMany('App\Models\Store', 'product_store')->withTimestamps();
    }

    /** Save the product's possible stores; the default store is always one of them. */
    public function syncStores($defaultStoreId, array $storeIds = []): void
    {
        $ids = collect($storeIds)->prepend($defaultStoreId)->filter()->map(fn ($id) => (int) $id)->unique();

        $this->stores()->sync(\App\Models\Store::whereIn('id', $ids)->pluck('id')->all());
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
        'store_id',
        'identification_number',
        'status',
    ];
}
