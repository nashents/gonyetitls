<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomsDeclaration extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Ordered customs workflow stages (spec section 27). Drives the
     * "Record Next Stage" UI action and doubles as each stage's milestone_code.
     * Real customs flows branch (a query/inspection is only sometimes raised),
     * so transitionStatus() never validates the target against "next" — this
     * array only powers the suggested-next-stage button, not a hard sequence.
     */
    /**
     * Common ASYCUDA Customs Procedure Codes (CPC): first two digits = the
     * procedure requested, last two = the previous procedure. Stored as the
     * code; anything else can be typed via the form's "Other" option.
     */
    public const CUSTOMS_PROCEDURES = [
        '4000' => 'Import for home use (duty paid)',
        '1000' => 'Permanent export',
        '8000' => 'Transit',
        '7000' => 'Warehousing (into bond)',
        '7100' => 'Warehouse to warehouse transfer',
        '4071' => 'Ex-warehouse for home use',
        '5300' => 'Temporary import (duty suspended)',
        '5100' => 'Inward processing',
        '2100' => 'Temporary export / outward processing',
        '3000' => 'Re-export',
        '3053' => 'Re-export of temporarily imported goods',
        '6000' => 'Re-import',
    ];

    /** "4000 - Import for home use (duty paid)", or the typed value for anything not listed. */
    public function customsProcedureLabel(): ?string
    {
        if (blank($this->customs_procedure)) {
            return null;
        }

        $label = self::CUSTOMS_PROCEDURES[$this->customs_procedure] ?? null;

        return $label ? $this->customs_procedure . ' - ' . $label : $this->customs_procedure;
    }

    const WORKFLOW_STAGES = [
        'instructions_received' => 'Instructions Received',
        'documents_outstanding' => 'Documents Outstanding',
        'documents_complete' => 'Documents Complete',
        'entry_preparation' => 'Entry Preparation',
        'entry_submitted' => 'Entry Submitted',
        'customs_processing' => 'Customs Processing',
        'assessment_issued' => 'Assessment Issued',
        'customs_query' => 'Customs Query',
        'query_response_submitted' => 'Query Response Submitted',
        'inspection_required' => 'Inspection Required',
        'inspection_scheduled' => 'Inspection Scheduled',
        'inspection_completed' => 'Inspection Completed',
        'duties_pending' => 'Duties Pending',
        'duties_paid' => 'Duties Paid',
        'customs_released' => 'Customs Released',
        'port_release_pending' => 'Port Release Pending',
        'port_released' => 'Port Released',
        'cleared' => 'Cleared',
    ];

    /**
     * Stages that also stamp a denormalized date column, so those dates stay
     * queryable without joining shipment_milestones.
     */
    const STAGE_DATE_COLUMNS = [
        'entry_submitted' => 'submission_date',
        'assessment_issued' => 'assessment_date',
        'inspection_completed' => 'inspection_date',
        'duties_paid' => 'payment_date',
        'customs_released' => 'release_date',
        'cleared' => 'clearance_date',
    ];

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function clearing_agent(){
        return $this->belongsTo('App\Models\ClearingAgent');
    }
    public function country(){
        return $this->belongsTo('App\Models\Country');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function declarant(){
        return $this->belongsTo('App\Models\User', 'declarant_id');
    }

    /** Staff declarant's name, else the typed-in name of an outside declarant. */
    public function declarantName(): ?string
    {
        $staff = $this->declarant ? trim($this->declarant->name . ' ' . $this->declarant->surname) : null;

        return $staff ?: ($this->declarant_name ?: null);
    }
    public function clearing_officer(){
        return $this->belongsTo('App\Models\User', 'clearing_officer_id');
    }
    public function lines(){
        return $this->hasMany('App\Models\CustomsDeclarationLine');
    }
    public function milestones(){
        return $this->hasMany('App\Models\ShipmentMilestone');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }

    public function nextWorkflowStage(): ?string
    {
        $codes = array_keys(self::WORKFLOW_STAGES);
        $currentIndex = array_search($this->status, $codes, true);

        if ($currentIndex === false || !isset($codes[$currentIndex + 1])) {
            return null;
        }

        return $codes[$currentIndex + 1];
    }

    protected $casts = [
        'declaration_date' => 'date',
        'submission_date' => 'date',
        'assessment_date' => 'date',
        'payment_date' => 'date',
        'inspection_date' => 'date',
        'release_date' => 'date',
        'clearance_date' => 'date',
    ];

    protected $fillable = [
        'shipment_id',
        'clearing_agent_id',
        'country_id',
        'currency_id',
        'declaration_number',
        'customs_office',
        'entry_number',
        'declaration_reference',
        'declaration_type',
        'customs_procedure',
        'declarant_id',
        'declarant_name',
        'declarant_code',
        'clearing_officer_id',
        'declaration_date',
        'submission_date',
        'assessment_date',
        'payment_date',
        'inspection_date',
        'release_date',
        'clearance_date',
        'status',
        'total_customs_value',
        'total_duty',
        'total_vat',
        'total_excise',
        'total_levies',
        'notes',
    ];
}
