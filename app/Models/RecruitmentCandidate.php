<?php

namespace App\Models;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentCandidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'employee_id',
        'department_id',
        'position_id',
        'applied_at',
        'first_name',
        'last_name',
        'dob',
        'age',
        'national_id',
        'drivers_license_number',
        'phone',
        'email',
        'years_experience',
        'source',
        'status',
        'screening_impression',
        'next_step',
        'notes',
        'screening_score',
        'road_test_score',
        'overall_score',
    ];

    protected $casts = [
        'applied_at' => 'date',
        'dob'        => 'date',
        'age'        => 'integer',
        'years_experience' => 'integer',
        'screening_score'  => 'integer',
        'road_test_score'  => 'integer',
        'overall_score'    => 'integer',
    ];

    // --- Relations ---

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function job_posting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function qualifications()
    {
        return $this->hasMany(RecruitmentQualification::class, 'candidate_id');
    }
    public function checks()
    {
        return $this->hasMany(RecruitmentCheck::class, 'candidate_id');
    }

    public function scores()
    {
        return $this->hasMany(RecruitmentScore::class, 'candidate_id');
    }

    public function decisions()
    {
        return $this->hasMany(RecruitmentDecision::class, 'candidate_id')->latest('decided_at');
    }

    // handy accessor
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
