<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key',
        'title',
        'description',
        'module',
        'type',
        'version',
        'released_at',
        'is_published',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'released_at' => 'datetime',
        'is_published' => 'boolean',
    ];

}
