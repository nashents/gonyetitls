<?php

namespace App\Http\Livewire\Freight\Jobs\Concerns;

use App\Models\Cargo;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Employee;
use App\Models\FreightServiceType;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\Quotation;
use App\Models\ShipmentParty;
use App\Models\UnitsOfMeasure;

/**
 * Dropdown lists + "refresh" links shared by the freight job Create and Edit
 * forms, so both offer exactly the same fields.
 */
trait FreightJobFormOptions
{
    public $customers;
    public $freight_service_types;
    public $currencies;
    public $destinations;
    public $officers;
    public $quotations;
    public $loading_points;
    public $offloading_points;
    public $cargos;
    public $uoms;
    public $partyOptions = [];
    public $partyModels = [];

    /** Role choices for a shipment party row. */
    public const PARTY_ROLES = [
        'shipper' => 'Shipper',
        'consignee' => 'Consignee',
        'notify_party' => 'Notify Party',
        'forwarding_agent' => 'Forwarding Agent',
        'overseas_agent' => 'Overseas Agent',
        'shipping_line' => 'Shipping Line',
        'airline' => 'Airline',
        'customs_broker' => 'Customs Broker',
        'warehouse' => 'Warehouse',
        'port' => 'Port',
        'terminal' => 'Terminal',
        'inspection_agent' => 'Inspection Agent',
        'container_depot' => 'Container Depot',
    ];

    /**
     * $keepCargoIds: inactive cargo types still used on the record being edited.
     * $keepOfficerUserIds: users already assigned on the record (e.g. a driver set
     * by older data) - still listed so Edit shows them, but never offered otherwise.
     */
    protected function loadFormOptions(int $companyId, array $keepCargoIds = [], array $keepOfficerUserIds = []): void
    {
        $this->customers = Customer::orderBy('name', 'asc')->get();
        $this->freight_service_types = FreightServiceType::orderBy('name', 'asc')->get();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
        $this->destinations = Destination::with('country')->orderBy('city', 'asc')->get();
        // Salesperson / officers: actual staff with a login - no drivers.
        $keepOfficerUserIds = array_values(array_filter($keepOfficerUserIds));
        $this->officers = Employee::where(function ($q) use ($companyId, $keepOfficerUserIds) {
                $q->where(fn ($staff) => $staff->where('company_id', $companyId)->activeStaff()->ofStaffType('admin'));
                if ($keepOfficerUserIds) {
                    $q->orWhereIn('user_id', $keepOfficerUserIds);
                }
            })
            ->whereNotNull('user_id')
            ->orderBy('name', 'asc')
            ->orderBy('surname', 'asc')
            ->get();
        $this->quotations = Quotation::with('customer')
            ->whereYear('date', date('Y'))
            ->latest()
            ->get();
        $this->loading_points = LoadingPoint::orderBy('name', 'asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name', 'asc')->get();
        $this->cargos = Cargo::selectable($keepCargoIds)->orderBy('name', 'asc')->get();
        $this->uoms = UnitsOfMeasure::orderBy('name', 'asc')->get();

        $this->partyOptions = ShipmentParty::partyTypeOptions();
        $this->partyModels = [];
        foreach ($this->partyOptions as $type) {
            $this->partyModels[$type] = $this->partyRecords($type);
        }
    }

    protected function partyRecords(string $type): array
    {
        $class = ShipmentParty::PARTY_MODELS[$type] ?? null;

        return $class ? $class::orderBy('name')->get(['id', 'name'])->toArray() : [];
    }

    /** Reloads one party type's list (after adding a new party in another tab). */
    protected function reloadPartyType(?string $type): void
    {
        if (! $type || ! isset($this->partyModels[$type])) {
            return;
        }

        $this->partyModels[$type] = $this->partyRecords($type);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => ucfirst(str_replace('_', ' ', $type)) . 's Refreshed Successfully!!.',
        ]);
    }

    public function refresh($category)
    {
        $reloads = [
            'customers' => ['Customers', fn () => $this->customers = Customer::orderBy('name', 'asc')->get()],
            'freight_service_types' => ['Service Types', fn () => $this->freight_service_types = FreightServiceType::orderBy('name', 'asc')->get()],
            'currencies' => ['Currencies', fn () => $this->currencies = Currency::orderBy('name', 'asc')->get()],
            'quotations' => ['Quotations', fn () => $this->quotations = Quotation::with('customer')->whereYear('date', date('Y'))->latest()->get()],
            'destinations' => ['Destinations', fn () => $this->destinations = Destination::with('country')->orderBy('city', 'asc')->get()],
            'cargos' => ['Cargo Types', fn () => $this->cargos = Cargo::selectable($this->keptCargoIds())->orderBy('name', 'asc')->get()],
            'uoms' => ['Units of Measure', fn () => $this->uoms = UnitsOfMeasure::orderBy('name', 'asc')->get()],
            'loading_points' => ['Loading Points', fn () => $this->loading_points = LoadingPoint::orderBy('name', 'asc')->get()],
            'offloading_points' => ['Offloading Points', fn () => $this->offloading_points = OffloadingPoint::orderBy('name', 'asc')->get()],
        ];

        if (! isset($reloads[$category])) {
            return;
        }

        [$label, $reload] = $reloads[$category];
        $reload();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => "{$label} Refreshed Successfully!!."]);
    }

    /** Cargo type ids that must stay selectable even if deactivated (Edit overrides). */
    protected function keptCargoIds(): array
    {
        return [];
    }

    protected function blankCargoRow(): array
    {
        return [
            'id' => null,
            'cargo_id' => null,
            'commodity' => null,
            'hs_code' => null,
            'quantity' => null,
            'uom' => null,
            'packages' => null,
            'gross_weight' => null,
            'is_dangerous_goods' => false,
        ];
    }

    protected function blankPartyRow(): array
    {
        return [
            'id' => null,
            'party_type' => null,
            'party_id' => null,
            'role' => null,
        ];
    }
}
