<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\ClearingAgent;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CustomsDeclaration;
use App\Models\CustomsDeclarationLine;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Freight\CustomsDeclarationService;
use Livewire\Component;

class CustomsDeclarations extends Component
{
    public $shipment;
    public $clearingAgents;
    public $countries;
    public $currencies;
    public $users;

    // Add Declaration form
    public $customs_office;
    public $entry_number;
    public $declaration_reference;
    public $declaration_type;
    public $customs_procedure;
    public $country_id;
    public $clearing_agent_id;
    public $declarant_id;
    public $clearing_officer_id;
    public $currency_id;
    public $declaration_date;

    // Add/Edit Line form
    public $editing_declaration_id;
    public $editing_line_id;
    public $line_shipment_cargo_id;
    public $line_hs_code;
    public $line_description;
    public $line_country_of_origin_id;
    public $line_quantity;
    public $line_uom;
    public $line_customs_value;
    public $line_currency_id;
    public $line_exchange_rate;
    public $line_duty_rate;
    public $line_duty_amount;
    public $line_vat_rate;
    public $line_vat_amount;
    public $line_excise_rate;
    public $line_excise_amount;
    public $line_levies_amount;
    public $line_is_preferential = false;
    public $line_trade_agreement;
    public $line_permit_reference;

    public $expanded_declaration_id;
    public $ad_hoc_milestone_declaration_id;
    public $ad_hoc_milestone_code;

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->clearingAgents = ClearingAgent::orderBy('name', 'asc')->get();
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->users = User::orderBy('name', 'asc')->get();
        $this->refreshShipment();
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with([
            'customs_declarations.lines',
            'customs_declarations.milestones',
            'customs_declarations.clearing_agent',
            'customs_declarations.declarant',
            'customs_declarations.clearing_officer',
            'customs_declarations.country',
            'customs_declarations.currency',
            'cargo_items',
        ])->findOrFail($this->shipment->id);
    }

    protected function rules()
    {
        return [
            'declaration_type' => 'nullable|string',
        ];
    }

    public function store(CustomsDeclarationService $service)
    {
        $this->validate();

        $service->create([
            'shipment_id' => $this->shipment->id,
            'customs_office' => $this->customs_office,
            'entry_number' => $this->entry_number,
            'declaration_reference' => $this->declaration_reference,
            'declaration_type' => $this->declaration_type,
            'customs_procedure' => $this->customs_procedure,
            'country_id' => $this->country_id ?: null,
            'clearing_agent_id' => $this->clearing_agent_id ?: null,
            'declarant_id' => $this->declarant_id ?: null,
            'clearing_officer_id' => $this->clearing_officer_id ?: null,
            'currency_id' => $this->currency_id ?: null,
            'declaration_date' => $this->declaration_date ?: null,
        ]);

        $this->reset(['customs_office', 'entry_number', 'declaration_reference', 'declaration_type', 'customs_procedure', 'country_id', 'clearing_agent_id', 'declarant_id', 'clearing_officer_id', 'currency_id', 'declaration_date']);

        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addDeclarationModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customs declaration added successfully!']);
    }

    public function advanceStage($declarationId, CustomsDeclarationService $service)
    {
        $declaration = CustomsDeclaration::findOrFail($declarationId);
        $next = $declaration->nextWorkflowStage();

        if ($next) {
            $service->transitionStatus($declaration, $next);
            $this->refreshShipment();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Declaration moved to ' . CustomsDeclaration::WORKFLOW_STAGES[$next]]);
        }
    }

    public function recordAdHocMilestone($declarationId, CustomsDeclarationService $service)
    {
        if (!$this->ad_hoc_milestone_code) {
            return;
        }

        $declaration = CustomsDeclaration::findOrFail($declarationId);
        $service->transitionStatus($declaration, $this->ad_hoc_milestone_code);

        $this->reset(['ad_hoc_milestone_code']);
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone recorded.']);
    }

    public function toggleExpand($declarationId)
    {
        $this->expanded_declaration_id = $this->expanded_declaration_id == $declarationId ? null : $declarationId;
    }

    public function showAddLine($declarationId)
    {
        $this->resetLineForm();
        $this->editing_declaration_id = $declarationId;
        $this->dispatchBrowserEvent('show-lineModal-' . $this->shipment->id);
    }

    public function editLine($lineId)
    {
        $line = CustomsDeclarationLine::findOrFail($lineId);
        $this->editing_declaration_id = $line->customs_declaration_id;
        $this->editing_line_id = $line->id;
        $this->line_shipment_cargo_id = $line->shipment_cargo_id;
        $this->line_hs_code = $line->hs_code;
        $this->line_description = $line->description;
        $this->line_country_of_origin_id = $line->country_of_origin_id;
        $this->line_quantity = $line->quantity;
        $this->line_uom = $line->uom;
        $this->line_customs_value = $line->customs_value;
        $this->line_currency_id = $line->currency_id;
        $this->line_exchange_rate = $line->exchange_rate;
        $this->line_duty_rate = $line->duty_rate;
        $this->line_duty_amount = $line->duty_amount;
        $this->line_vat_rate = $line->vat_rate;
        $this->line_vat_amount = $line->vat_amount;
        $this->line_excise_rate = $line->excise_rate;
        $this->line_excise_amount = $line->excise_amount;
        $this->line_levies_amount = $line->levies_amount;
        $this->line_is_preferential = $line->is_preferential;
        $this->line_trade_agreement = $line->trade_agreement;
        $this->line_permit_reference = $line->permit_reference;

        $this->dispatchBrowserEvent('show-lineModal-' . $this->shipment->id);
    }

    private function lineData(): array
    {
        return [
            'shipment_cargo_id' => $this->line_shipment_cargo_id ?: null,
            'hs_code' => $this->line_hs_code,
            'description' => $this->line_description,
            'country_of_origin_id' => $this->line_country_of_origin_id ?: null,
            'quantity' => $this->line_quantity ?: null,
            'uom' => $this->line_uom,
            'customs_value' => $this->line_customs_value ?: null,
            'currency_id' => $this->line_currency_id ?: null,
            'exchange_rate' => $this->line_exchange_rate ?: null,
            'duty_rate' => $this->line_duty_rate ?: null,
            'duty_amount' => $this->line_duty_amount ?: 0,
            'vat_rate' => $this->line_vat_rate ?: null,
            'vat_amount' => $this->line_vat_amount ?: 0,
            'excise_rate' => $this->line_excise_rate ?: null,
            'excise_amount' => $this->line_excise_amount ?: 0,
            'levies_amount' => $this->line_levies_amount ?: 0,
            'is_preferential' => (bool) $this->line_is_preferential,
            'trade_agreement' => $this->line_trade_agreement,
            'permit_reference' => $this->line_permit_reference,
        ];
    }

    public function saveLine(CustomsDeclarationService $service)
    {
        if ($this->editing_line_id) {
            $service->updateLine(CustomsDeclarationLine::findOrFail($this->editing_line_id), $this->lineData());
        } else {
            $declaration = CustomsDeclaration::findOrFail($this->editing_declaration_id);
            $service->addLine($declaration, $this->lineData());
        }

        $this->resetLineForm();
        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-lineModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Declaration line saved.']);
    }

    public function deleteLine($lineId, CustomsDeclarationService $service)
    {
        $service->deleteLine(CustomsDeclarationLine::findOrFail($lineId));
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Declaration line removed.']);
    }

    private function resetLineForm()
    {
        $this->reset([
            'editing_line_id', 'line_shipment_cargo_id', 'line_hs_code', 'line_description', 'line_country_of_origin_id',
            'line_quantity', 'line_uom', 'line_customs_value', 'line_currency_id', 'line_exchange_rate',
            'line_duty_rate', 'line_duty_amount', 'line_vat_rate', 'line_vat_amount', 'line_excise_rate',
            'line_excise_amount', 'line_levies_amount', 'line_is_preferential', 'line_trade_agreement', 'line_permit_reference',
        ]);
    }

    public function render()
    {
        return view('livewire.freight.shipments.customs-declarations', [
            'workflowStages' => CustomsDeclaration::WORKFLOW_STAGES,
        ]);
    }
}
