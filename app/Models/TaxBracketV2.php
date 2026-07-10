<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TaxBracketV2 extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tax_brackets_v2';

    protected $fillable = [
        'statutory_deduction_type_id', 'currency_id',
        'effective_from', 'effective_to',
        'lower_band', 'upper_band',
        'rate_percentage', 'cumulative_tax_at_lower_band',
        'aids_levy_percentage', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'effective_from'               => 'date',
        'effective_to'                 => 'date',
        'lower_band'                   => 'decimal:4',
        'upper_band'                   => 'decimal:4',
        'rate_percentage'              => 'decimal:4',
        'cumulative_tax_at_lower_band' => 'decimal:4',
        'aids_levy_percentage'         => 'decimal:4',
        'sort_order'                   => 'integer',
    ];

    public function deductionType()
    {
        return $this->belongsTo(StatutoryDeductionType::class, 'statutory_deduction_type_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
