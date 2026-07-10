<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class EmployeeQualification extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'candidate_id',
        'verified_by',
        'verified_at',
        'qualification_id',
        'level',
        'date_awarded',
        'status',
        'certificate_path',
        'expires_at',
    ];

    protected $casts = [
        'verified_at' => 'date',
    ];

    public function candidate()
    {
        return $this->belongsTo(Employee::class, 'candidate_id');
    }

    public function verified_by()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
