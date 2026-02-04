<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginAudit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'logged_in_at',
        'ip',
        'user_agent',
        'country',
        'country_code',
        'region',
        'city',
        'zip',
        'timezone',
        'lat',
        'lng',
        'provider',
        'source',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
