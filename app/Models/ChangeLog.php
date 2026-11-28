<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The system's "What's new" release notes (synced by SyncChangeLog) - the same
 * for every company, so NOT company-filtered (platform data, see
 * config/tenancy.php platform_tables). No SoftDeletes: change_logs has no
 * deleted_at column (every model query failed with it).
 */
class ChangeLog extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

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
