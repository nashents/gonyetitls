<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TyreAssignment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    protected $appends = ['travelled_km', 'remaining_km', 'remaining_pct'];

    const AXLES = [
        'Steering Axle', 'Drive Axle', 'Front Axle', 'Middle Axle', 'Rear Axle', 'Diff Axle', 'Spare Wheel',
    ];

    // value => label ("Front" is stored for Front Left on older records).
    const POSITIONS = [
        'Front' => 'Front Left',
        'Front Right' => 'Front Right',
        'Front Left Inside' => 'Front Left Inside',
        'Front Left Outside' => 'Front Left Outside',
        'Front Right Inside' => 'Front Right Inside',
        'Front Right Outside' => 'Front Right Outside',
        'Middle Left Inside' => 'Middle Left Inside',
        'Middle Left Outside' => 'Middle Left Outside',
        'Middle Right Inside' => 'Middle Right Inside',
        'Middle Right Outside' => 'Middle Right Outside',
        'Rear Left Inside' => 'Rear Left Inside',
        'Rear Left Outside' => 'Rear Left Outside',
        'Rear Right Inside' => 'Rear Right Inside',
        'Rear Right Outside' => 'Rear Right Outside',
        'Spare Wheel' => 'Spare Wheel',
    ];

    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function ticket_inventory(){
        return $this->belongsTo('App\Models\TicketInventory');
    }
     public function checklist_results(){
        return $this->hasMany('App\Models\ChecklistResult');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function tyre_dispatch(){
        return $this->hasOne('App\Models\TyreDispatch');
    }

    protected $fillable=[
        'user_id',
        'vehicle_id',
        'horse_id',
        'trailer_id',
        'tyre_id',
        'starting_odometer',
        'ending_odometer',
        'position',
        'axle',
        'status',
        'unassigned_date',
        'unassignment_reason',
        'unassigned_by',
    ];

    public function unassignedBy(){
        return $this->belongsTo('App\Models\User', 'unassigned_by');
    }

    // A tyre can only sit on one vehicle at a time.
    public static function activeForTyre($tyreId, $exceptId = null)
    {
        return static::where('tyre_id', $tyreId)
            ->where('status', 1)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }

    // Spares can be carried more than one at a time; every other wheel slot takes one tyre.
    public static function isSpareSlot($axle, $position)
    {
        return $axle === 'Spare Wheel' || $position === 'Spare Wheel';
    }

    // The active assignment occupying an axle + position on an asset, if any.
    public static function activeAtPosition($column, $assetId, $axle, $position, $exceptId = null)
    {
        if (!$assetId || blank($axle) || blank($position) || static::isSpareSlot($axle, $position)) {
            return null;
        }

        return static::with('tyre')
            ->where($column, $assetId)
            ->where('axle', $axle)
            ->where('position', $position)
            ->where('status', 1)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->first();
    }

    public static function positionTakenMessage(TyreAssignment $active)
    {
        $label = static::POSITIONS[$active->position] ?? $active->position;
        $serial = optional($active->tyre)->serial_number;

        return "{$active->axle} {$label} already has an active assignment"
            .($serial ? " (SN# {$serial})" : "")
            .". Unassign that tyre first.";
    }

    public function locationLabel()
    {
        return optional($this->horse)->identifier_label
            ?? optional($this->trailer)->identifier_label
            ?? optional($this->vehicle)->identifier_label;
    }

    public static function alreadyAssignedMessage(TyreAssignment $active, $tyreLabel = null)
    {
        $location = $active->locationLabel();

        return ($tyreLabel ? "Tyre {$tyreLabel} is" : "This tyre is")." already assigned"
            .($location ? " to {$location}" : "")
            .". Unassign it first before re-assigning.";
    }

      public function getTravelledKmAttribute()
    {
        $start = $this->starting_odometer;
        if (is_null($start)) {
            return null;
        }

        // If removed, freeze distance at removal; else use current horse odometer.
        $end = $this->ending_odometer ?? optional($this->horse)->mileage;

        if (is_null($end)) {
            return null;
        }

        return max(0, (int)$end - (int)$start);
    }

    public function getRemainingKmAttribute()
    {
        $std = optional($this->tyre)->life_span;
        $travelled = $this->travelled_km;

        if (is_null($std) || is_null($travelled)) {
            return null;
        }

        return max(0, (int)$std - (int)$travelled);
    }

    public function getRemainingPctAttribute()
    {
        $std = optional($this->tyre)->life_span;
        $rem = $this->remaining_km;

        if (empty($std) || is_null($rem)) {
            return null;
        }

        return round(($rem / $std) * 100, 1); // e.g., 63.4
    }
}
