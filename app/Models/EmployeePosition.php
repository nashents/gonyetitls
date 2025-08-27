<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeePosition extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

        protected $fillable = [
        'employee_id',
        'job_title_id',
        'grade_id',
        'start_date',
        'end_date',
        'change_reason',
        'changed_by',
        'remarks'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scope to fetch current record
    public function scopeCurrent($query)
    {
        return $query->whereNull('end_date');
    }
}
