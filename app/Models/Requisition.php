<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requisition extends Model implements Auditable
{
    use Concerns\GuardsOperationalAssets;
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    // POs raised from this requisition (Purchases form sets purchases.requisition_id)
    public function purchase_orders(){
        return $this->hasMany('App\Models\Purchase');
    }

    /**
     * A PO is "attached" either way round: picked on the requisition
     * (requisitions.purchase_id) or the PO created against it (purchases.requisition_id).
     */
    public function scopeWithPurchaseOrder($query)
    {
        return $query->where(fn ($q) => $q->whereHas('purchase')->orWhereHas('purchase_orders'));
    }

    public function scopeWithoutPurchaseOrder($query)
    {
        return $query->whereDoesntHave('purchase')->whereDoesntHave('purchase_orders');
    }

    public function hasPurchaseOrder(): bool
    {
        return $this->purchase !== null || $this->purchase_orders->isNotEmpty();
    }

    /**
     * Requisitions > Index list — shared by the screen and RequisitionExport so
     * downloads always match what's on screen.
     *
     * $filters: date_column, from, to, search, type (po_requisition|payment_requisition),
     * po_status (attached|awaiting).
     */
    public function scopeListFilter($query, User $user, array $filters)
    {
        // created_at (Created At) or date (the requisition's own Y-m-d date)
        $dateColumn = in_array($filters['date_column'] ?? null, ['created_at', 'date'], true)
            ? $filters['date_column'] : 'created_at';
        $from       = $filters['from'] ?? null;
        $to         = $filters['to'] ?? null;
        $search     = trim((string) ($filters['search'] ?? ''));
        $type       = $filters['type'] ?? '';
        $poStatus   = $filters['po_status'] ?? '';

        $user->loadMissing(['employee.departments', 'roles']);
        $departments = $user->employee?->departments ?? collect();

        $isFinanceOrSuper = $departments->contains('name', 'Finance')
            || $user->roles->contains('name', 'Super Admin');

        // Non-finance/non-super: restrict to their departments
        if (! $isFinanceOrSuper) {
            $query->whereIn('department_id', $departments->pluck('id')->all());
        }

        if ($type === 'po_requisition') {
            $query->where('type', 'po_requisition');
        } elseif ($type === 'payment_requisition') {
            // Untyped (legacy) rows display as payment requisitions
            $query->where(fn ($q) => $q->where('type', 'payment_requisition')->orWhereNull('type'));
        }

        // PO progress only applies to PO requisitions
        if ($poStatus === 'attached') {
            $query->where('type', 'po_requisition')->withPurchaseOrder();
        } elseif ($poStatus === 'awaiting') {
            $query->where('type', 'po_requisition')->withoutPurchaseOrder();
        }

        // Date range OR default current month/year — skipped while searching or
        // checking PO progress so older matches / outstanding POs aren't hidden.
        // whereDate so the To day is included in full for created_at
        if (filled($from) && filled($to)) {
            $query->whereDate($dateColumn, '>=', $from)->whereDate($dateColumn, '<=', $to);
        } elseif (filled($from)) {
            $query->whereDate($dateColumn, '>=', $from);
        } elseif (filled($to)) {
            $query->whereDate($dateColumn, '<=', $to);
        } elseif ($search === '' && ! filled($poStatus)) {
            $query->whereMonth($dateColumn, now()->month)
                ->whereYear($dateColumn, now()->year);
        }

        if ($search !== '') {
            $like = "%{$search}%";

            $query->where(function ($q) use ($like) {
                $q->where('requisition_number', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhere('date', 'like', $like)
                    ->orWhere('total', 'like', $like)
                    ->orWhereHas('requisition_items.expense', fn ($qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('trip', function ($qq) use ($like) {
                        $qq->where('trip_number', 'like', $like)
                            ->orWhereHas('horse', fn ($hhh) => $hhh->where('registration_number', 'like', $like));
                    })
                    ->orWhereHas('employee', fn ($qq) => $qq->where(DB::raw("concat(name, ' ', surname)"), 'like', $like))
                    ->orWhereHas('currency', fn ($qq) => $qq->where('name', 'like', $like));
            });
        }

        return $query->orderBy($dateColumn, 'desc')->orderBy('id', 'desc');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
    public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function attached_employee(){
        return $this->belongsTo('App\Models\Employee', 'attached_employee_id');
    }
}
