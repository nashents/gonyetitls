<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model implements Auditable
{
    use \App\Models\Concerns\BelongsToCompany; // multi-tenancy (docs/multi-tenancy-plan.md)

    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function quotation_items(){
        return $this->hasMany('App\Models\QuotationItem');
    }
     public function additional_costs(){
        return $this->hasMany('App\Models\AdditionalCost');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bank_accounts(){
        return $this->belongsToMany('App\Models\BankAccount');
    }
    public function discount(){
        return $this->hasOne('App\Models\Discount');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function invoices(){
        return $this->hasMany('App\Models\Invoice');
    }
    public function sales_order_user(){
        return $this->belongsTo('App\Models\User', 'sales_order_by_id');
    }

    // A quotation becomes a sales order once it has been converted (sales_order_number set).
    public function isSalesOrder(): bool
    {
        return ! empty($this->sales_order_number);
    }

    /**
     * Next sales order number + sequence, e.g. quotation "ABCQ00012" -> "ABCSO00001".
     * The sequence runs across all sales orders, independent of the quotation number.
     *
     * @return array [number, seq]
     */
    public function nextSalesOrderNumber(): array
    {
        $prefix = preg_match('/^(.*?)Q\d+$/', (string) $this->quotation_number, $m) ? $m[1] : '';
        $seq = (int) static::withTrashed()->max('sales_order_seq');

        do {
            $seq++;
            $number = $prefix.'SO'.str_pad($seq, 5, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('sales_order_number', $number)->exists());

        return [$number, $seq];
    }

    /**
     * Convert to a sales order: assigns the SO number and stores the optional SO reference.
     */
    public function convertToSalesOrder($reference = null, $date = null, $userId = null): self
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($reference, $date, $userId) {
            $quotation = static::lockForUpdate()->findOrFail($this->id);
            if ($quotation->isSalesOrder()) {
                return $quotation;
            }

            [$number, $seq] = $quotation->nextSalesOrderNumber();
            $quotation->sales_order_number = $number;
            $quotation->sales_order_seq = $seq;
            $quotation->sales_order_ref = trim((string) $reference) !== '' ? trim($reference) : null;
            $quotation->sales_order_date = $date ?: date('Y-m-d');
            $quotation->sales_order_by_id = $userId;
            $quotation->save();

            return $quotation;
        });
    }

    /**
     * Filters shared by the Quotations list and its exports.
     * Keys: date_column, from, to, search, document_type (all|quotation|sales_order).
     */
    public function scopeListFilter($query, array $filters)
    {
        $column = in_array($filters['date_column'] ?? null, ['created_at', 'date', 'sales_order_date'])
            ? $filters['date_column'] : 'created_at';
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));
        $type = $filters['document_type'] ?? 'all';

        if ($type === 'quotation') {
            $query->whereNull('sales_order_number');
        } elseif ($type === 'sales_order') {
            $query->whereNotNull('sales_order_number');
        }

        if ($from && $to) {
            $query->whereDate($column, '>=', $from)->whereDate($column, '<=', $to);
        } elseif ($search === '') {
            // No date range and no search: current month only.
            $query->whereMonth($column, date('m'))->whereYear($column, date('Y'));
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('quotation_number', 'like', $like)
                    ->orWhere('sales_order_number', 'like', $like)
                    ->orWhere('custom_ref', 'like', $like)
                    ->orWhere('sales_order_ref', 'like', $like)
                    ->orWhere('date', 'like', $like)
                    ->orWhere('expiry', 'like', $like)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('currency', fn ($c) => $c->where('name', 'like', $like));
            });
        }

        return $query->orderBy($column, 'desc')->orderBy('id', 'desc');
    }
}
