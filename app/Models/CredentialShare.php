<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One login-details send (email or WhatsApp) and who initiated it.
 */
class CredentialShare extends Model
{
    protected $fillable = [
        'employee_id',
        'user_id',
        'shared_by_id',
        'channel',
        'recipient',
        'bulk',
        'ip_address',
    ];

    protected $casts = [
        'bulk' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shared_by()
    {
        return $this->belongsTo(User::class, 'shared_by_id');
    }
}
