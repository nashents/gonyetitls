<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAuditTemplate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function sections(){
        return $this->hasMany('App\Models\SheqAuditSection')->orderBy('sort_order','asc');
    }
    public function items(){
        return $this->hasManyThrough('App\Models\SheqAuditItem','App\Models\SheqAuditSection');
    }
    public function sheq_audits(){
        return $this->hasMany('App\Models\SheqAudit');
    }
    public function possibleTotal(){
        return $this->items->sum('possible_mark');
    }

    /**
     * Only the template's creator, is_admin users and Super Admins may build
     * (add/edit/delete sections and requirements of) the checklist.
     * Pass $isPrivileged when checking many rows to avoid a role query each.
     */
    public function canBeBuiltBy($user, ?bool $isPrivileged = null): bool
    {
        if (!$user) {
            return false;
        }
        $isPrivileged = $isPrivileged ?? ($user->is_admin() || $user->isSuperAdmin());

        return $isPrivileged || ($this->user_id && (int) $this->user_id === (int) $user->id);
    }
}
