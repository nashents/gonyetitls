<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TripNote extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'trip_id',
        'user_id',
        'task_type',
        'body',
        'area',
        'by_time',
        'notify',
        'latitude',
        'longitude',
        'location_description',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    /**
     * Smart-comment quick templates — the button label is the key, the
     * value is the canned message with [placeholder] tokens the composer
     * fills in from the Area/By/Notify fields before it's saved.
     */
    public const TASK_TEMPLATES = [
        'to_leave'          => 'To leave [area] by [time], notify [notify]',
        'to_arrive'         => 'To arrive in [area] by [time], notify [notify]',
        'notify_on_arrival' => 'Notify [notify] on arrival at [area]',
        'to_load'           => 'To load at [area] by [time]',
        'to_offload'        => 'To offload at [area] by [time]',
    ];

    public const TASK_LABELS = [
        'to_leave'          => 'To Leave',
        'to_arrive'         => 'To Arrive',
        'notify_on_arrival' => 'Notify On Arrival',
        'to_load'           => 'To Load',
        'to_offload'        => 'To Offload',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
