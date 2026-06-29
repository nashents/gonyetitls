<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePensionEnrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'pension_scheme_id', 'effective_from', 'effective_to',
        'voluntary_additional_percentage', 'voluntary_additional_fixed_amount',
        'member_number', 'enrolled_by', 'notes',
    ];

    protected $casts = [
        'effective_from'                     => 'date',
        'effective_to'                       => 'date',
        'voluntary_additional_percentage'    => 'decimal:4',
        'voluntary_additional_fixed_amount'  => 'decimal:4',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function pensionScheme()
    {
        return $this->belongsTo(PensionScheme::class, 'pension_scheme_id');
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }
}
