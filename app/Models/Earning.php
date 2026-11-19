<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Payroll earnings (Accommodation, Transport, ...) added on top of basic pay.
 * Payroll uses earnings; Allowances are for Transport / driver trip expenses.
 */
class Earning extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id', 'currency_id', 'name', 'calculate_by', 'amount', 'percentage',
        'description', 'status', 'taxable',
    ];

    protected $casts = [
        'taxable' => 'boolean',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function salary_items(){
        return $this->hasMany('App\Models\SalaryItem');
    }
    public function payroll_salary_items(){
        return $this->hasMany('App\Models\PayrollSalaryItem');
    }

    /** A percentage earning is always a % of BASIC (gross includes earnings, so it can't be the base). */
    public function isPercentage(): bool
    {
        return $this->calculate_by === 'percentage';
    }

    /** The amount this earning comes to on a salary with this basic (null = no default set). */
    public function amountFor(float $basic): ?float
    {
        if ($this->isPercentage()) {
            return is_numeric($this->percentage) ? round($basic * (float) $this->percentage / 100, 2) : null;
        }

        return is_numeric($this->amount) ? (float) $this->amount : null;
    }
}
