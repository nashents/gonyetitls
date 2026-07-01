<?php

namespace App\Models;

use App\Models\Bill;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
            'company_id',
            'invoice_id',
            'bill_id',
            'payment_id',
            'payroll_run_id',
            'is_manual',
            'journal_number',
            'date',
            'reference',
            'description',
            'status',
            'created_by_id',
            'posted_by_id',
            'posted_at',
        ];

    protected $casts = [
        'date'      => 'date',
        'posted_at' => 'datetime',
        'is_manual' => 'boolean',
    ];

    public function journal_entry_lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function posted_by()
    {
        return $this->belongsTo(User::class, 'posted_by_id');
    }
}
