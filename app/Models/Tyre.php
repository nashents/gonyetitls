<?php

namespace App\Models;

use App\Models\ChecklistResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Tyre extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
      public function transfer_items(){
        return $this->hasMany('App\Models\TransferItem');
    }
      public function bill_expenses(){
        return $this->hasMany('App\Models\BillExpense');
    }
     public function checklist_results(){
        return $this->hasMany('App\Models\ChecklistResult');
    }
      public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
     public function goods_received(){
        return $this->belongsTo('App\Models\GoodsReceived');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
    public function dispatch_items(){
        return $this->hasMany('App\Models\DispatchItem');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function transfers(){
        return $this->hasMany('App\Models\Transfer');
    }
    public function ticket_inventories(){
        return $this->hasMany('App\Models\TicketInventory');
    }
    public function dispose(){
        return $this->hasOne('App\Models\Dispose');
    }
    public function breakages(){
        return $this->hasMany('App\Models\Breakage');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
     public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function tyre_details(){
        return $this->hasMany('App\Models\TyreDetail');
    }
     public function retread_items(){
        return $this->hasMany('App\Models\RetreadItem');
    }
    public function retread_tyres(){
        return $this->hasMany('App\Models\RetreadTyre');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function activeAssignment()
    {
        // latest active assignment
        return $this->hasOne(TyreAssignment::class, 'tyre_id')
            ->where('status', 1)
            ->latestOfMany();
    }

    // Serial for display; blank serials say so instead of disappearing.
    public function getSerialLabelAttribute(): string
    {
        return filled($this->serial_number) ? (string) $this->serial_number : 'Not entered';
    }

    const LIST_LOCATIONS = [
        'in_store' => 'In Store',
        'fitted' => 'Fitted',
        'unfitted' => 'Out of Store · Not Fitted',
        'disposed' => 'Disposed',
    ];

    /**
     * Tyres list + its exports: search, location (LIST_LOCATIONS key) and equipment
     * (type Horse/Trailer/Vehicle, optional unit id) on the tyre's active assignment.
     * With no search or location, disposed tyres stay hidden.
     */
    public function scopeListFilter($query, array $filters)
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $location = $filters['location'] ?? null;
        $column = ['Horse' => 'horse_id', 'Trailer' => 'trailer_id', 'Vehicle' => 'vehicle_id'][$filters['equipment_type'] ?? ''] ?? null;
        $equipmentId = $filters['equipment_id'] ?? null;

        return $query->where('retread', 0)
            ->when($search === '' && !$location, fn ($q) => $q->where('disposed', 0))
            ->when($location === 'in_store', fn ($q) => $q->inStock())
            ->when($location === 'fitted', fn ($q) => $q->whereHas('tyre_assignments', fn ($ta) => $ta->where('status', 1)))
            ->when($location === 'unfitted', fn ($q) => $q
                ->where(fn ($b) => $b->whereNull('balance')->orWhere('balance', '<=', 0))
                ->where(fn ($d) => $d->whereNull('disposed')->orWhere('disposed', 0))
                ->whereDoesntHave('tyre_assignments', fn ($ta) => $ta->where('status', 1)))
            ->when($location === 'disposed', fn ($q) => $q->where('disposed', 1))
            ->when($column, fn ($q) => $q->whereHas('tyre_assignments', fn ($ta) => $ta->where('status', 1)
                ->when($equipmentId, fn ($x) => $x->where($column, $equipmentId), fn ($x) => $x->whereNotNull($column))))
            ->when($search !== '', function ($q) use ($search) {
                $like = "%{$search}%";

                $q->where(function ($qq) use ($like) {
                    $qq->where('tyre_number', 'like', $like)
                        ->orWhere('serial_number', 'like', $like)
                        ->orWhere('purchase_date', 'like', $like)
                        ->orWhere('type', 'like', $like)
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $like))
                        ->orWhereHas('product.brand', fn ($b) => $b->where('name', 'like', $like))
                        ->orWhereHas('currency', fn ($c) => $c->where('name', 'like', $like))
                        ->orWhereHas('store', fn ($s) => $s->where('name', 'like', $like))
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', $like))
                        ->orWhereHas('goods_received', fn ($g) => $g->where('goods_received_number', 'like', $like))
                        ->orWhereHas('tyre_assignments', function ($ta) use ($like) {
                            $ta->where('status', 1)
                                ->where(function ($x) use ($like) {
                                    $x->whereHas('horse', fn ($h) => $h->where('registration_number', 'like', $like))
                                        ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', $like))
                                        ->orWhereHas('trailer', fn ($t) => $t->where('registration_number', 'like', $like));
                                });
                        });
                });
            });
    }

    // Dispatchable: available, has stock left and not sitting on a truck.
    public function scopeInStock($query)
    {
        return $query->where('status', 1)
            ->where('balance', '>', 0)
            ->where(fn ($q) => $q->whereNull('disposed')->orWhere('disposed', 0))
            ->whereDoesntHave('tyre_assignments', fn ($q) => $q->where('status', 1));
    }

    // Can go on a new dispatch: in stock and not already on another pending dispatch.
    public function scopeDispatchable($query)
    {
        return $query->inStock()
            ->whereDoesntHave('dispatch_items.dispatch', fn ($d) => $d->where('authorization', 'pending'));
    }

    // The pending dispatch already holding this tyre, if any.
    public function pendingDispatch(): ?Dispatch
    {
        return Dispatch::where('authorization', 'pending')
            ->whereHas('dispatch_items', fn ($q) => $q->where('tyre_id', $this->id))
            ->first();
    }

    /**
     * Can be fitted in Tyre Assignments: out of stores and not on a unit, and it left stores
     * properly — an approved dispatch, an earlier fitting (taken off a unit), or imported with qty 0.
     */
    public function scopeAssignable($query)
    {
        return $query->where(fn ($q) => $q->whereNull('balance')->orWhere('balance', '<=', 0))
            ->where(fn ($q) => $q->whereNull('disposed')->orWhere('disposed', 0))
            ->whereDoesntHave('tyre_assignments', fn ($q) => $q->where('status', 1))
            ->where(function ($q) {
                $q->whereHas('dispatch_items.dispatch', fn ($d) => $d->where('authorization', 'approved'))
                    ->orWhereHas('tyre_assignments')
                    ->orWhere('qty', 0);
            });
    }

    // Why this tyre can't be fitted right now, or null when it can.
    public function assignmentBlocker(): ?string
    {
        $label = $this->serial_number ?: $this->tyre_number;

        if ($this->disposed) {
            return "Tyre {$label} is disposed.";
        }
        if ($active = TyreAssignment::activeForTyre($this->id)) {
            return TyreAssignment::alreadyAssignedMessage($active, $label);
        }
        if (!static::assignable()->whereKey($this->id)->exists()) {
            return "Tyre {$label} is still in stores. Dispatch it first, then fit it here.";
        }

        return null;
    }

    // Latest approved dispatch line that took this tyre out of stores.
    public function lastDispatchItem()
    {
        return $this->dispatch_items()
            ->whereHas('dispatch', fn ($d) => $d->where('authorization', 'approved'))
            ->with('dispatch.horse', 'dispatch.trailer', 'dispatch.vehicle')
            ->latest('id')
            ->first();
    }

    // For lists: where an unfitted, out-of-store tyre went.
    public function awaitingFitmentLabel(): ?string
    {
        $item = $this->lastDispatchItem();
        if (!$item || !$item->dispatch) {
            return null;
        }

        $dispatch = $item->dispatch;
        $unit = optional($dispatch->horse)->identifier_label
            ?? optional($dispatch->trailer)->identifier_label
            ?? optional($dispatch->vehicle)->identifier_label;

        return trim(($unit ? "On {$unit}" : 'Dispatched')." · {$dispatch->dispatch_number}");
    }

     public function checklistResults()
    {
        return $this->hasMany(ChecklistResult::class, 'tyre_id');
    }

    public function latestChecklistResult()
    {
        // Laravel 8+ supports this.
        return $this->hasOne(ChecklistResult::class, 'tyre_id')->latestOfMany();
        // If your "latest" should be by a specific date column, you can do:
        // return $this->hasOne(ChecklistResult::class, 'tyre_id')->latestOfMany('created_at');
    }

     protected $dates = ['purchase_date'];

      public function getAgeAttribute()
    {
        if (!$this->purchase_date) {
            return null;
        }

        $purchase = Carbon::parse($this->purchase_date);
        $now = Carbon::now();

        $diff = $purchase->diff($now);

        // Format smartly: "X years Y months" or "X months" or "X days"
        if ($diff->y > 0) {
            return "{$diff->y} year(s) {$diff->m} month(s)";
        } elseif ($diff->m > 0) {
            return "{$diff->m} month(s)";
        } else {
            return "{$diff->d} day(s)";
        }
    }

    protected $fillable = [
        'currency_id',
        'store_id',
        'product_id',
        'serial_number',
        'amount',
        'subtotal',
        'subtotal_incl',
        'total',
        'type',
        'width',
        'aspect_ratio',
        'diameter',
        'qty',
        'purchase_date',
        'status',
        'disposed',
    ];

}
