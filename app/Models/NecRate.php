<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class NecRate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'nec_category_id', 'currency_id', 'effective_from', 'effective_to',
        'employee_percentage', 'employer_percentage',
        'employee_fixed_amount', 'employer_fixed_amount',
        'earnings_ceiling', 'calculation_basis', 'notes', 'created_by',
    ];

    protected $casts = [
        'effective_from'      => 'date',
        'effective_to'        => 'date',
        'employee_percentage' => 'decimal:4',
        'employer_percentage' => 'decimal:4',
        'employee_fixed_amount' => 'decimal:4',
        'employer_fixed_amount' => 'decimal:4',
        'earnings_ceiling'    => 'decimal:4',
    ];

    public function category()
    {
        return $this->belongsTo(NecCategory::class, 'nec_category_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
