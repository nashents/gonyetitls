<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BankReconciliationItem extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'bank_reconciliation_id',
        'bank_statement_line_id',
        'journal_entry_line_id',
        'item_type',
        'amount',
        'description',
        'cleared_at',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'cleared_at' => 'datetime',
    ];

    public function bank_reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function bank_statement_line()
    {
        return $this->belongsTo(BankStatementLine::class);
    }

    public function journal_entry_line()
    {
        return $this->belongsTo(JournalEntryLine::class);
    }
}
