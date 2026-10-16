<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrailerLink extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }

    /** Links that include the given trailer on either side. */
    public function scopeForTrailer($query, $trailerId)
    {
        return $query->where(function ($q) use ($trailerId) {
            $q->where('trailer_a', $trailerId)->orWhere('trailer_b', $trailerId);
        });
    }

    /** The trailer id linked to $trailerId, or null when it has no link. */
    public static function partnerOf($trailerId): ?int
    {
        if (empty($trailerId)) {
            return null;
        }
        $link = static::forTrailer($trailerId)->latest()->first();
        if (!$link) {
            return null;
        }
        $partner = (string) $link->trailer_a === (string) $trailerId ? $link->trailer_b : $link->trailer_a;
        return $partner ? (int) $partner : null;
    }

    /** The link joining exactly these two trailers (either order), if any. */
    public static function between($a, $b): ?self
    {
        return static::where(function ($q) use ($a, $b) {
            $q->where('trailer_a', $a)->where('trailer_b', $b);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('trailer_a', $b)->where('trailer_b', $a);
        })->first();
    }

}
