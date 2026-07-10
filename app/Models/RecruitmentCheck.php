<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RecruitmentCheck extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'candidate_id',
        'checked_by',
        'type',
        'result',
        'comment',
        'checked_at',
        'attachment_path',
    ];

    protected $casts = [
        'checked_at' => 'date',
    ];

    public function candidate()
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'candidate_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
