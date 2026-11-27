<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Setting extends Model implements Auditable
{
    use \App\Models\Concerns\BelongsToCompanyOrShared; // multi-tenancy (docs/multi-tenancy-plan.md)

    use HasFactory;
    use \OwenIt\Auditing\Auditable;
}
