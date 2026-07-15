<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SheqAudit extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function template(){
        return $this->belongsTo('App\Models\SheqAuditTemplate','sheq_audit_template_id');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function lead_auditor(){
        return $this->belongsTo('App\Models\Employee','lead_auditor_id');
    }
    public function auditee(){
        return $this->belongsTo('App\Models\Employee','auditee_id');
    }
    public function responses(){
        return $this->hasMany('App\Models\SheqAuditResponse');
    }
    public function actions(){
        return $this->morphMany('App\Models\SheqAction','actionable');
    }

    public function responseFor($itemId){
        return $this->responses->firstWhere('sheq_audit_item_id', $itemId);
    }

    public function sectionActualTotal($section){
        $itemIds = $section->items->pluck('id');
        return $this->responses->whereIn('sheq_audit_item_id', $itemIds)->sum('actual_mark');
    }

    public function actualTotal(){
        return $this->responses->sum('actual_mark');
    }

    public function possibleTotal(){
        return $this->template ? $this->template->possibleTotal() : 0;
    }

    public function percentageScore(){
        $possible = $this->possibleTotal();
        if ($possible == 0) {
            return 0;
        }
        return round(($this->actualTotal() / $possible) * 100, 1);
    }

    public function findings(){
        return $this->responses->filter(function($response){
            return in_array($response->grading, ['NC','OFI']) || $response->findings;
        });
    }

    public function nonConformityCount(){
        return $this->responses->where('grading','NC')->count();
    }

    public function ofiCount(){
        return $this->responses->where('grading','OFI')->count();
    }
}
