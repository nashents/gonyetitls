<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripCmrDetail extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'insurer_name',
        'insurance_policy_number',
        'insurance_cover_amount',
        'special_agreements',
        'marks_and_numbers',
        'number_of_packages',
        'freight_payment_terms',
    ];

    protected $casts = [
        'insurance_cover_amount' => 'decimal:2',
        'number_of_packages'     => 'integer',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
