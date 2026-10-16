<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A valid value of the Sage PROJECT "Depature/Destination Location" picklists
 * (ENUM custom fields DEPATURE_LOCATION / DESTINATION_LOCATION), pulled from Sage.
 * Gonyeti destinations map to one of these; the trip push sends the mapped name.
 */
class SageProjectLocation extends Model
{
    protected $fillable = [
        'name',
        'for_departure',
        'for_destination',
    ];

    protected $casts = [
        'for_departure'   => 'boolean',
        'for_destination' => 'boolean',
    ];
}
