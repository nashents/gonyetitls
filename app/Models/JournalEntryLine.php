<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Horse;
use App\Models\JournalEntry;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class JournalEntryLine extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'branch_id',
        'customer_id',
        'vendor_id',
        'horse_id',
        'employee_id',
        'driver_id',
        'transporter_id',
        'vehicle_id',
        'trailer_id',
        'description',
        'debit',
        'exchange_debit',
        'credit',
        'exchange_credit',
        'currency_id',
        'exchange_rate',
        'bank_reconciliation_id',
        'cleared_at',
    ];

    protected $casts = [
        'debit'          => 'decimal:2',
        'credit'         => 'decimal:2',
        'exchange_debit' => 'decimal:2',
        'exchange_credit'=> 'decimal:2',
        'exchange_rate'  => 'decimal:6',
        'cleared_at'     => 'datetime',
    ];

    public function journal_entry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function horse()
    {
        return $this->belongsTo(Horse::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trailer()
    {
        return $this->belongsTo(Trailer::class);
    }

    public function bank_reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function isCleared(): bool
    {
        return $this->cleared_at !== null;
    }
}
