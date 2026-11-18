<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/** One per-user module lock — see App\Support\ModuleAccess. */
class ModuleAccessBlock extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id', 'module_group_id', 'module_id', 'created_by'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module_group()
    {
        return $this->belongsTo(ModuleGroup::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
