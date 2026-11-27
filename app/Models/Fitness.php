<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;

class Fitness extends Model  implements Auditable
{
    use \App\Models\Concerns\BelongsToCompany; // multi-tenancy (docs/multi-tenancy-plan.md)

    use Concerns\GuardsOperationalAssets;
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function fitnesses(){
        return $this->belongsToMany('App\Models\Fitness');
    }
    public function recipients(){
        return $this->belongsToMany('App\Models\Recipient');
    }
    public function reminder_item(){
        return $this->belongsTo('App\Models\ReminderItem');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }

    /**
     * What a reminder can be "for": type label => column. A reminder has at
     * most one of these set; none = "Other" (a personal reminder).
     */
    public const SUBJECTS = [
        'Horse'    => 'horse_id',
        'Vehicle'  => 'vehicle_id',
        'Trailer'  => 'trailer_id',
        'Employee' => 'employee_id',
        'Customer' => 'customer_id',
        'Vendor'   => 'vendor_id',
    ];

    /** Every subject column null except the one for $type. */
    public static function subjectColumns(?string $type, $id): array
    {
        $cols = array_fill_keys(array_values(self::SUBJECTS), null);
        if ($type && isset(self::SUBJECTS[$type]) && filled($id)) {
            $cols[self::SUBJECTS[$type]] = $id;
        }
        return $cols;
    }

    /** "Horse", "Customer", ... or "Other". */
    public function subjectType(): string
    {
        foreach (self::SUBJECTS as $type => $col) {
            if (!is_null($this->{$col})) {
                return $type;
            }
        }
        return 'Other';
    }

    /** Human label of what the reminder is for, e.g. "customer Acme Ltd". */
    public function subjectLabel(): string
    {
        switch ($this->subjectType()) {
            case 'Horse':
                return trim(optional(optional($this->horse)->horse_make)->name . ' ' . optional(optional($this->horse)->horse_model)->name . ' ' . optional($this->horse)->registration_number);
            case 'Vehicle':
                return trim(optional(optional($this->vehicle)->vehicle_make)->name . ' ' . optional(optional($this->vehicle)->vehicle_model)->name . ' ' . optional($this->vehicle)->registration_number);
            case 'Trailer':
                return 'trailer ' . optional($this->trailer)->registration_number;
            case 'Employee':
                return trim(optional($this->employee)->name . ' ' . optional($this->employee)->surname);
            case 'Customer':
                return 'customer ' . optional($this->customer)->name;
            case 'Vendor':
                return 'vendor ' . optional($this->vendor)->name;
        }
        return '';
    }

    public function scopeVisibleTo($query)
    {
        // Fleet and customer/vendor reminders are shared; personal ("Other")
        // and staff reminders only show to the person who set them.
        return $query->where(function ($q) {
            $q->whereNotNull('horse_id')
            ->orWhereNotNull('vehicle_id')
            ->orWhereNotNull('trailer_id')
            ->orWhereNotNull('customer_id')
            ->orWhereNotNull('vendor_id')
            ->orWhere(function ($q) {
                $q->whereNull('horse_id')
                    ->whereNull('vehicle_id')
                    ->whereNull('trailer_id')
                    ->whereNull('customer_id')
                    ->whereNull('vendor_id')
                    ->where('user_id', Auth::id());
            });
        });
    }

    protected $casts = [
        'expires_at'               => 'datetime',
        'first_reminder_at'        => 'datetime',
        'second_reminder_at'       => 'datetime',
        'third_reminder_at'        => 'datetime',
        'fourth_reminder_at'        => 'datetime',
        // 'issued_at'          => 'datetime',

        'first_reminder_at_status' => 'boolean',
        'second_reminder_at_status'=> 'boolean',
        'third_reminder_at_status' => 'boolean',
        'fourth_reminder_at_status' => 'boolean',
        'closed'                   => 'boolean',
        'cc'                       => 'boolean',
        'notify_contacts'          => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'company_id',
        'vehicle_id',
        'horse_id',
        'trailer_id',
        'employee_id',
        'customer_id',
        'vendor_id',
        'reminder_item_id',
        'name',
        'issued_at',
        'expires_at',
        'reminder_at',
        'status',
        'first_reminder_at',
        'second_reminder_at',
        'third_reminder_at',
        'fourth_reminder_at',
        'first_reminder_at_status',
        'second_reminder_at_status',
        'third_reminder_at_status',
        'fourth_reminder_at_status',
        'closed',
        'cc',
        'notify_contacts',
    ];

    /** Copy-list people picked for this reminder (empty = whole list). */
    public function selectedCopies()
    {
        return $this->belongsToMany(ReminderCopy::class, 'fitness_reminder_copy')->withTimestamps();
    }

    /**
     * Copy-list people who get this reminder: none unless cc is on; then the
     * picked people, or (none picked) everyone active on the owner's list.
     * Inactive or deleted copies never receive.
     */
    public function copyRecipients()
    {
        if (!$this->cc) {
            return collect();
        }

        // Check the raw pivot: if every picked person was since removed from
        // the list (soft-deleted), this must mean "nobody", not "everyone".
        if (\Illuminate\Support\Facades\DB::table('fitness_reminder_copy')->where('fitness_id', $this->id)->exists()) {
            return $this->selectedCopies()->where('status', true)->get();
        }

        return ReminderCopy::where('user_id', $this->user_id)->where('status', true)->get();
    }

    /** Only customer / vendor reminders can email outside contacts. */
    public function canNotifyContacts(): bool
    {
        return !is_null($this->customer_id) || !is_null($this->vendor_id);
    }

    /**
     * Customer-facing recipients: the customer's / vendor's contacts with a
     * valid email, else the customer's / vendor's own email. Unique, lowercase.
     *
     * @return array<string, string> email => display name
     */
    public static function contactEmailsFor(?string $type, $id): array
    {
        if (!in_array($type, ['Customer', 'Vendor'], true) || !$id) {
            return [];
        }

        $party = $type === 'Customer' ? Customer::with('contacts')->find($id) : Vendor::with('contacts')->find($id);
        if (!$party) {
            return [];
        }

        $emails = [];
        foreach ($party->contacts as $contact) {
            $email = strtolower(trim((string) $contact->email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = trim($contact->name . ' ' . $contact->surname) ?: $party->name;
            }
        }

        if (!$emails) {
            $email = strtolower(trim((string) $party->email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = $party->name;
            }
        }

        return $emails;
    }

    public function contactEmails(): array
    {
        $type = $this->subjectType();
        return self::contactEmailsFor($type, $type === 'Customer' ? $this->customer_id : $this->vendor_id);
    }

    

}
