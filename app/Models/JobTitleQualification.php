<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobTitleQualification extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function job_title(){
        return $this->belongsTo('App\Models\JobTitle');
    } 
    public function qualification(){
        return $this->belongsTo('App\Models\Qualification');
    } 

    protected $fillable = [
        'job_title_id',
        'qualification_id',
        'mandatory',
        'min_level',
        'weight',
        'min_score',
    ];

    
}
