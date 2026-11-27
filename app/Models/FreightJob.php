<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightJob extends Model implements Auditable
{
    use \App\Models\Concerns\BelongsToCompany; // multi-tenancy (docs/multi-tenancy-plan.md)

    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const INCOTERMS = [
        'EXW' => 'EXW - Ex Works',
        'FCA' => 'FCA - Free Carrier',
        'CPT' => 'CPT - Carriage Paid To',
        'CIP' => 'CIP - Carriage and Insurance Paid To',
        'DAP' => 'DAP - Delivered At Place',
        'DPU' => 'DPU - Delivered At Place Unloaded',
        'DDP' => 'DDP - Delivered Duty Paid',
        'FAS' => 'FAS - Free Alongside Ship',
        'FOB' => 'FOB - Free On Board',
        'CFR' => 'CFR - Cost and Freight',
        'CIF' => 'CIF - Cost, Insurance and Freight',
    ];

    /**
     * LCL: client jobs follow their consolidation's master job through the
     * shared container stages (see ConsolidationStatusSync for the rules).
     */
    protected static function booted()
    {
        static::updated(function (FreightJob $job) {
            if ($job->wasChanged('status')) {
                \App\Services\Freight\ConsolidationStatusSync::follow($job, $job->getOriginal('status'), $job->status);
            }
        });
    }

    /** Customer may be blank only on a Consolidation (master container) job. */
    const NO_CUSTOMER_LABEL = 'Consolidation (no customer)';

    public function getCustomerLabelAttribute(): string
    {
        return $this->customer?->name ?? self::NO_CUSTOMER_LABEL;
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function freight_service_type(){
        // withTrashed: a deleted service type still shows its name on existing jobs.
        return $this->belongsTo('App\Models\FreightServiceType')->withTrashed();
    }
    public function salesperson(){
        return $this->belongsTo('App\Models\User', 'salesperson_id');
    }
    public function operations_officer(){
        return $this->belongsTo('App\Models\User', 'operations_officer_id');
    }
    public function clearing_officer(){
        return $this->belongsTo('App\Models\User', 'clearing_officer_id');
    }
    public function origin_country(){
        return $this->belongsTo('App\Models\Country', 'origin_country_id');
    }
    public function destination_country(){
        return $this->belongsTo('App\Models\Country', 'destination_country_id');
    }
    public function fromDestination(){
        return $this->belongsTo('App\Models\Destination', 'from');
    }
    public function toDestination(){
        return $this->belongsTo('App\Models\Destination', 'to');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function quotation(){
        return $this->belongsTo('App\Models\Quotation');
    }
    public function shipments(){
        return $this->hasMany('App\Models\Shipment');
    }
    /** Custom follow-up reminders (Reminders tab). */
    public function reminders(){
        return $this->hasMany(FreightJobReminder::class);
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function costs(){
        return $this->hasMany('App\Models\FreightCost');
    }
    public function charges(){
        return $this->hasMany('App\Models\FreightCharge');
    }
    public function authorized_by(){
        return $this->belongsTo('App\Models\User', 'authorized_by_id');
    }

    /**
     * Freight Jobs list filters - shared by the list (Freight\Jobs\Index) and
     * FreightJobsExport so an export always matches what is on screen.
     * Keys: search, status, authorization, freight_service_type_id, customer_id.
     */
    /**
     * Company-specific (multi-tenancy safe): the signed-in user's company only.
     * No user (CLI / data backup) = no restriction.
     */
    public function scopeForCurrentCompany($query)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user) {
            return $query;
        }

        return $query->where($query->getModel()->getTable() . '.company_id', $user->employee?->company_id ?? $user->company?->id);
    }

    public function scopeListFilter($query, array $filters)
    {
        $query->forCurrentCompany();

        if (filled($search = trim((string) ($filters['search'] ?? '')))) {
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                    ->orWhere('customer_reference', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('fromDestination', fn ($q2) => $q2->where('city', 'like', "%{$search}%"))
                    ->orWhereHas('toDestination', fn ($q2) => $q2->where('city', 'like', "%{$search}%"));
            });
        }

        foreach (['status', 'authorization', 'freight_service_type_id', 'customer_id'] as $column) {
            if (filled($filters[$column] ?? null)) {
                $query->where($column, $filters[$column]);
            }
        }

        // A user-picked sort (Freight Jobs "Sort by", remembered per user).
        if (filled($filters['sort'] ?? null) && isset(self::SORTS[$filters['sort']])) {
            return $this->applyListSort($query, $filters['sort'], ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc');
        }

        // Approved / Rejected lists show the latest decisions first.
        if (in_array($filters['authorization'] ?? null, ['approved', 'rejected'], true)) {
            $query->orderByDesc('authorization_date');
        }

        return $query->latest()->orderByDesc('id');
    }

    /** Freight Jobs list "Sort by" options (key => label). Blank = default order. */
    public const SORTS = [
        'created_at' => 'Date Created',
        'job_number' => 'Job #',
        'customer' => 'Customer',
        'etd' => 'ETD',
        'eta' => 'ETA',
        'shipping_line' => 'Shipping Line',
        'origin' => 'Origin',
        'destination' => 'Destination',
        'status' => 'Status',
        'authorization_date' => 'Authorization Date',
    ];

    /**
     * ETD/ETA = the job's earliest shipment date; shipping line = first
     * container's line, else the first sea leg's. Jobs with no value always
     * sort last (either direction); id breaks ties so pages never skip rows.
     */
    private function applyListSort($query, string $sort, string $dir)
    {
        $jobs = $this->getTable();
        $sub = match ($sort) {
            'etd', 'eta' => "(SELECT MIN(s.{$sort}) FROM shipments s WHERE s.freight_job_id = {$jobs}.id AND s.deleted_at IS NULL)",
            'customer' => "(SELECT c.name FROM customers c WHERE c.id = {$jobs}.customer_id)",
            'origin' => "(SELECT d.city FROM destinations d WHERE d.id = {$jobs}.`from`)",
            'destination' => "(SELECT d.city FROM destinations d WHERE d.id = {$jobs}.`to`)",
            'shipping_line' => "COALESCE("
                . "(SELECT sl.name FROM shipping_containers sc JOIN shipments s ON s.id = sc.shipment_id JOIN shipping_lines sl ON sl.id = sc.shipping_line_id"
                . " WHERE s.freight_job_id = {$jobs}.id AND s.deleted_at IS NULL AND sc.deleted_at IS NULL ORDER BY sc.id LIMIT 1),"
                . "(SELECT sl.name FROM shipment_legs l JOIN shipments s ON s.id = l.shipment_id JOIN shipping_lines sl ON sl.id = l.shipping_line_id"
                . " WHERE s.freight_job_id = {$jobs}.id AND s.deleted_at IS NULL AND l.deleted_at IS NULL ORDER BY s.id, l.sequence LIMIT 1))",
            default => null,
        };

        if ($sub) {
            $query->orderByRaw("{$sub} IS NULL")->orderByRaw("{$sub} {$dir}");
        } elseif ($sort === 'status') {
            // Lifecycle order, not alphabetical.
            $statuses = "'draft','confirmed','in_progress','customs_clearance','port_storage','transport_arranged','delivered','invoiced','closed','cancelled'";
            $query->orderByRaw("FIELD({$jobs}.status, {$statuses}) {$dir}");
        } elseif ($sort === 'authorization_date') {
            $query->orderByRaw("{$jobs}.authorization_date IS NULL")->orderBy("{$jobs}.authorization_date", $dir);
        } else {
            $query->orderBy("{$jobs}.{$sort}", $dir);
        }

        return $query->orderBy("{$jobs}.id", $dir);
    }

    /** Statuses a job may hold before it is approved (see FreightJobAuthorizationService). */
    public const PRE_APPROVAL_STATUSES = ['draft', 'cancelled'];

    public function isApproved(): bool
    {
        return $this->authorization === 'approved';
    }

    protected $casts = [
        'opened_at' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
        'authorization_date' => 'datetime',
        'estimated_revenue' => 'float',
        'estimated_cost' => 'float',
        'estimated_margin' => 'float',
        'actual_revenue' => 'float',
        'actual_cost' => 'float',
        'actual_margin' => 'float',
    ];

    protected $fillable = [
        'user_id',
        'job_number',
        'customer_reference',
        'company_id',
        'customer_id',
        'freight_service_type_id',
        'salesperson_id',
        'operations_officer_id',
        'clearing_officer_id',
        'import_export_type',
        'shipment_type',
        'primary_transport_mode',
        'origin',
        'destination',
        'origin_country_id',
        'destination_country_id',
        'from',
        'to',
        'incoterm',
        'currency_id',
        'quotation_id',
        'estimated_revenue',
        'estimated_cost',
        'estimated_margin',
        'actual_revenue',
        'actual_cost',
        'actual_margin',
        'status',
        'authorization',
        'authorized_by_id',
        'authorization_date',
        'authorization_comments',
        'opened_at',
        'completed_at',
        'closed_at',
        'notes',
    ];
}
