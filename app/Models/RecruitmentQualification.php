<?php

namespace App\Models;

use App\Models\RecruitmentCandidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentQualification extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->belongsTo(RecruitmentCandidate::class, 'candidate_id');
    }
    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function verified_by()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
