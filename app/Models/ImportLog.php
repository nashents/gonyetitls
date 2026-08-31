<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    protected $casts = [
        'skipped_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'company_id',
        'import_type',
        'original_filename',
        'rows_processed',
        'rows_created',
        'rows_skipped',
        'skipped_details',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];
}
