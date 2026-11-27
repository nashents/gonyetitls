<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\ClearingAgent;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CustomsDeclaration;
use App\Models\CustomsDeclarationLine;
use App\Models\CustomsOffice;
use Illuminate\Validation\Rule;
use App\Models\Employee;
use App\Models\Shipment;
use App\Models\UnitsOfMeasure;
use App\Services\Freight\CustomsDeclarationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

class CustomsDeclarations extends Component
{
    use FreightEmbeddedPagination;

    public $shipment;
    public $clearingAgents;
    public $countries;
    public $currencies;
    public $users;
    public $uoms;

    public $customsOffices;

    // Add Declaration form
    public $customs_office; // office name from the dropdown, or NEW_OFFICE to type one
    public $new_customs_office;
    public $entry_number;
    public $declaration_reference;
    public $declaration_type;
    public $customs_procedure; // a CustomsDeclaration::CUSTOMS_PROCEDURES code, or OTHER_PROCEDURE to type one
    public $other_customs_procedure;
    public $country_id;
    public $clearing_agent_id;
    public $declarant_id;
    public $declarant_name; // outside declarant (not in the system), used when declarant_id is blank
    public $declarant_code;
    // Staff declarant's customs code: typed when missing or on "Update", written to their employee record on save
    public $staff_code_input;
    public bool $editing_staff_code = false;
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
    public $ad_hoc_milestone_date;

    /** [declaration id => 'Y-m-d\TH:i'] - when the next stage actually happened; prefilled with now. */
    public $stage_dates = [];

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->clearingAgents = ClearingAgent::orderBy('name', 'asc')->get();
        $this->countries = Country::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->loadStaff();
        $this->uoms = UnitsOfMeasure::orderBy('name', 'asc')->get();
        $this->loadCustomsOffices();
        $this->refreshShipment();
    }

    /**
     * Declarant / Clearing Officer = active non-driver employees with a login
     * in the job's company only (multi-tenancy safe), same as the job's officer
     * dropdowns. Not User::all(): that includes portal logins.
     */
    private function loadStaff(): void
    {
        $companyId = $this->shipment?->freight_job?->company_id ?? Auth::user()->employee?->company_id;
        $this->users = Employee::where('company_id', $companyId)->activeStaff()->ofStaffType('admin') // no drivers
            ->whereNotNull('user_id')
            ->orderBy('name', 'asc')
            ->orderBy('surname', 'asc')
            ->get(['id', 'user_id', 'name', 'surname', 'customs_code']);
    }

    public const NEW_OFFICE = '__new__';
    public const OTHER_PROCEDURE = '__other__';

    private function loadCustomsOffices()
    {
        $this->customsOffices = CustomsOffice::where('is_active', true)->orderBy('name', 'asc')->get(['id', 'name']);
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with([
            'customs_declarations.lines',
            'customs_declarations.milestones.creator',
            'customs_declarations.clearing_agent',
            'customs_declarations.declarant',
            'customs_declarations.clearing_officer',
            'customs_declarations.country',
            'customs_declarations.currency',
            'cargo_items',
        ])->findOrFail($this->shipment->id);

        // Prefill each "next stage" date with now (kept if the user already changed it).
        $now = now()->format('Y-m-d\TH:i');
        foreach ($this->shipment->customs_declarations as $declaration) {
            if ($declaration->nextWorkflowStage() && blank($this->stage_dates[$declaration->id] ?? null)) {
                $this->stage_dates[$declaration->id] = $now;
            }
        }
        $this->ad_hoc_milestone_date = $this->ad_hoc_milestone_date ?: $now;
    }

    protected function rules()
    {
        return [
            'declaration_type' => 'nullable|string',
            'declarant_name' => 'nullable|string|max:255',
            'declarant_code' => 'nullable|string|max:100',
            'staff_code_input' => 'nullable|string|max:100',
            'new_customs_office' => [Rule::requiredIf($this->customs_office === self::NEW_OFFICE), 'nullable', 'string', 'max:255'],
            'other_customs_procedure' => [Rule::requiredIf($this->customs_procedure === self::OTHER_PROCEDURE), 'nullable', 'string', 'max:100'],
        ];
    }

    protected $validationAttributes = ['new_customs_office' => 'customs office name', 'other_customs_procedure' => 'customs procedure'];

    /** New declarant picked: start from their recorded code, read-only. */
    public function updatedDeclarantId()
    {
        $this->editing_staff_code = false;
        $this->staff_code_input = null;
        $this->resetErrorBag('staff_code_input');
    }

    public function cancelStaffCodeUpdate()
    {
        $this->editing_staff_code = false;
        $this->staff_code_input = null;
        $this->resetErrorBag('staff_code_input');
    }

    /**
     * The staff declarant's customs code for this declaration. A code typed
     * here (none on record yet, or "Update") is written back to that one
     * field on their employee record, so it's reused next time - no access
     * to the rest of the HR record needed.
     */
    private function resolveStaffCustomsCode($userId): ?string
    {
        $employee = Employee::where('user_id', $userId)->first();
        if (! $employee) {
            return null;
        }

        $typed = trim((string) $this->staff_code_input);
        $canType = $this->editing_staff_code || blank($employee->customs_code);

        if ($canType && $typed !== '' && $typed !== $employee->customs_code) {
            $employee->customs_code = $typed;
            $employee->save();
        }

        return $employee->customs_code ?: null;
    }

    public function store(CustomsDeclarationService $service)
    {
        $this->validate();

        // "+ Add new" saves the office once so it can be selected next time.
        $office = $this->customs_office;
        if ($office === self::NEW_OFFICE) {
            $office = CustomsOffice::findOrCreateByName($this->new_customs_office, $this->country_id ?: null, Auth::id())->name;
        }

        $service->create([
            'shipment_id' => $this->shipment->id,
            'customs_office' => $office ?: null,
            'entry_number' => $this->entry_number,
            'declaration_reference' => $this->declaration_reference,
            'declaration_type' => $this->declaration_type,
            'customs_procedure' => $this->customs_procedure === self::OTHER_PROCEDURE
                ? (trim((string) $this->other_customs_procedure) ?: null)
                : ($this->customs_procedure ?: null),
            'country_id' => $this->country_id ?: null,
            'clearing_agent_id' => $this->clearing_agent_id ?: null,
            'declarant_id' => $this->declarant_id ?: null,
            // A staff declarant wins; the typed name/code are only for someone not in the
            // system. A staff declarant's code comes from their employee record (copied, so
            // later edits to the employee don't rewrite this declaration).
            'declarant_name' => $this->declarant_id ? null : (trim((string) $this->declarant_name) ?: null),
            'declarant_code' => $this->declarant_id
                ? $this->resolveStaffCustomsCode($this->declarant_id)
                : (trim((string) $this->declarant_code) ?: null),
            'clearing_officer_id' => $this->clearing_officer_id ?: null,
            'currency_id' => $this->currency_id ?: null,
            'declaration_date' => $this->declaration_date ?: null,
        ]);

        $this->loadCustomsOffices();
        $this->reset(['customs_office', 'new_customs_office', 'entry_number', 'declaration_reference', 'declaration_type', 'customs_procedure', 'other_customs_procedure', 'country_id', 'clearing_agent_id', 'declarant_id', 'declarant_name', 'declarant_code', 'staff_code_input', 'editing_staff_code', 'clearing_officer_id', 'currency_id', 'declaration_date']);
        $this->loadStaff(); // picks up a customs code just saved to an employee record

        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addDeclarationModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Customs declaration added successfully!']);
    }

    public function advanceStage($declarationId, CustomsDeclarationService $service)
    {
        $declaration = CustomsDeclaration::findOrFail($declarationId);
        $next = $declaration->nextWorkflowStage();

        if ($next) {
            $eventAt = $this->eventDate("stage_dates.{$declarationId}", $this->stage_dates[$declarationId] ?? null);

            $service->transitionStatus($declaration, $next, ['actual_at' => $eventAt]);
            unset($this->stage_dates[$declarationId]);
            $this->refreshShipment();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Declaration moved to ' . CustomsDeclaration::WORKFLOW_STAGES[$next] . ' on ' . $eventAt->format('d M Y H:i')]);
        }
    }

    public function recordAdHocMilestone($declarationId, CustomsDeclarationService $service)
    {
        if (!$this->ad_hoc_milestone_code) {
            return;
        }

        $eventAt = $this->eventDate('ad_hoc_milestone_date', $this->ad_hoc_milestone_date);

        $declaration = CustomsDeclaration::findOrFail($declarationId);
        $service->transitionStatus($declaration, $this->ad_hoc_milestone_code, ['actual_at' => $eventAt]);

        $this->reset(['ad_hoc_milestone_code']);
        $this->ad_hoc_milestone_date = now()->format('Y-m-d\TH:i');
        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Milestone recorded on ' . $eventAt->format('d M Y H:i') . '.']);
    }

    /**
     * When a stage actually happened, for statuses recorded late. The box is
     * prefilled with "now"; a blank one also means now. Never in the future.
     */
    private function eventDate(string $field, $value): Carbon
    {
        if ($value === null || $value === '') {
            return now();
        }

        $this->validate(
            [$field => 'date|before_or_equal:now'],
            [$field . '.before_or_equal' => 'The stage date cannot be in the future.'],
            [$field => 'stage date']
        );

        return Carbon::parse($value);
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

    public function refresh($category)
    {
        if ($category === 'clearing_agents') {
            $this->clearingAgents = ClearingAgent::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Clearing Agents Refreshed Successfully!!.']);
        } elseif ($category === 'countries') {
            $this->countries = Country::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Countries Refreshed Successfully!!.']);
        } elseif ($category === 'currencies') {
            $this->currencies = Currency::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Currencies Refreshed Successfully!!.']);
        } elseif ($category === 'uoms') {
            $this->uoms = UnitsOfMeasure::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Units of Measure Refreshed Successfully!!.']);
        }
    }

    public function render()
    {
        return view('livewire.freight.shipments.customs-declarations', [
            'declarationRows' => $this->paginateRows($this->shipment->customs_declarations->sortBy('id')),
            'workflowStages' => CustomsDeclaration::WORKFLOW_STAGES,
        ]);
    }
}
