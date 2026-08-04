<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class BankReconciliation extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'account_id',
        'period_start',
        'period_end',
        'statement_closing_balance',
        'book_closing_balance',
        'adjusted_bank_balance',
        'adjusted_book_balance',
        'difference',
        'status',
        'prepared_by_id',
        'reviewed_by_id',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'period_start'              => 'date',
        'period_end'                => 'date',
        'statement_closing_balance' => 'decimal:2',
        'book_closing_balance'      => 'decimal:2',
        'adjusted_bank_balance'     => 'decimal:2',
        'adjusted_book_balance'     => 'decimal:2',
        'difference'                => 'decimal:2',
        'completed_at'              => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function prepared_by()
    {
        return $this->belongsTo(User::class, 'prepared_by_id');
    }

    public function reviewed_by()
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function items()
    {
        return $this->hasMany(BankReconciliationItem::class);
    }

    public function journal_entry_lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
