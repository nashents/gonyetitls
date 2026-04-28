<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ProblemCategory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

     public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    protected $fillable = [
        'name',
        'description'
    ];
}
