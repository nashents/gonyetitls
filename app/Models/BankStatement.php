<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class BankStatement extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'company_id',
        'bank_account_id',
        'currency_id',
        'file_name',
        'file_format',
        'period_start',
        'period_end',
        'opening_balance',
        'closing_balance',
        'imported_by_id',
        'status',
        'imported_count',
        'duplicate_count',
        'error_message',
    ];

    protected $casts = [
        'period_start'    => 'date',
        'period_end'      => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function imported_by()
    {
        return $this->belongsTo(User::class, 'imported_by_id');
    }

    public function lines()
    {
        return $this->hasMany(BankStatementLine::class);
    }
}
