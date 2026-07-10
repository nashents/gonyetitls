<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class EmployeeNecAssignment extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'employee_id', 'nec_category_id', 'effective_from', 'effective_to',
        'assigned_by', 'notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function necCategory()
    {
        return $this->belongsTo(NecCategory::class, 'nec_category_id');
    }
}
