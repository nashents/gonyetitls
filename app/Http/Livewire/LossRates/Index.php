<?php

namespace App\Http\Livewire\LossRates;

use App\Exports\LossRatesExport;
use App\Models\Cargo;
use App\Models\Customer;
use App\Services\LossRateService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Trip Management > Master > Loss Rates.
 *  - Allowances: customer x cargo matrix of allowable loss %. Each cell is
 *    the customer + cargo agreement; "All cargos" is the customer's own %,
 *    the top row the cargo default. Blank cells show the inherited value.
 *  - Rates: chargeable loss rate per cargo for Own Fleet vs Subcontractors.
 * Percentages are stored as a percent; the page can take them as fractions
 * (0.003) - that is only an entry/display unit.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $tab = 'allowances';       // allowances | rates
    public $unit = 'percent';         // percent | fraction (entry/display only)
    public $search = '';              // customer name (allowances) / cargo name (rates)
    public $cargoType = '';           // '' | Liquid | Solid ... (matrix columns)
    public $cargoSearch = '';         // matrix column filter
    public $gapsOnly = false;

    /** Inline values in the CURRENT unit. */
    public $allowances = [];          // [customerId][cargoId|'all']
    public $cargoDefaults = [];       // [cargoId]
    public $rates = [];               // [cargoId]['own'|'sub']

    public function mount()
    {
        $this->tab = request('tab') === 'rates' ? 'rates' : 'allowances';
        $this->search = (string) request('search', '');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingGapsOnly() { $this->resetPage(); }

    public function setTab($tab)
    {
        $this->tab = $tab === 'rates' ? 'rates' : 'allowances';
        $this->search = '';
        $this->resetPage();
    }

    /** Switching the unit converts what is already typed. */
    public function updatedUnit($unit)
    {
        $this->unit = $unit === 'fraction' ? 'fraction' : 'percent';
        $factor = $this->unit === 'fraction' ? 0.01 : 100;
        $convert = fn ($v) => is_numeric($v) ? $this->clean($v * $factor) : $v;

        foreach ($this->allowances as $customerId => $cells) {
            foreach ($cells as $key => $value) {
                $this->allowances[$customerId][$key] = $convert($value);
            }
        }
        foreach ($this->cargoDefaults as $cargoId => $value) {
            $this->cargoDefaults[$cargoId] = $convert($value);
        }
    }

    private function service(): LossRateService
    {
        return app(LossRateService::class);
    }

    /** Stored percent -> current unit. */
    private function toUnit($pct)
    {
        if (! is_numeric($pct)) {
            return null;
        }

        return $this->clean($this->unit === 'fraction' ? $pct / 100 : $pct);
    }

    /** Current unit -> stored percent. */
    private function toPercent($value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return round($this->unit === 'fraction' ? $value * 100 : (float) $value, 6);
    }

    private function clean($number)
    {
        return (float) rtrim(rtrim(number_format((float) $number, 8, '.', ''), '0'), '.');
    }

    private function same($a, $b): bool
    {
        if (! is_numeric($a) || ! is_numeric($b)) {
            return ! is_numeric($a) && ! is_numeric($b);
        }

        return abs((float) $a - (float) $b) < 0.0000001;
    }

    public function saveAllowances()
    {
        $max = $this->unit === 'fraction' ? 1 : 100;
        $this->validate([
            'allowances.*.*' => "nullable|numeric|min:0|max:{$max}",
            'cargoDefaults.*' => "nullable|numeric|min:0|max:{$max}",
        ], [], ['allowances.*.*' => 'allowable loss', 'cargoDefaults.*' => 'cargo default']);

        $service = $this->service();
        $agreements = $service->agreements();
        $changed = 0;

        DB::transaction(function () use ($service, $agreements, &$changed) {
            foreach ($this->cargoDefaults as $cargoId => $value) {
                $cargo = Cargo::find($cargoId);
                $pct = $this->toPercent($value);
                if ($cargo && ! $this->same($cargo->allowable_loss_percentage, $pct)) {
                    $cargo->allowable_loss_percentage = $pct;
                    $cargo->save();
                    $changed++;
                }
            }

            foreach ($this->allowances as $customerId => $cells) {
                foreach ($cells as $key => $value) {
                    $pct = $this->toPercent($value);

                    if ($key === 'all') {
                        $customer = Customer::find($customerId);
                        if ($customer && ! $this->same($customer->allowable_loss_percentage, $pct)) {
                            $customer->allowable_loss_percentage = $pct;
                            $customer->save();
                            $changed++;
                        }
                        continue;
                    }

                    if (! $this->same($agreements[$customerId . '|' . $key] ?? null, $pct)) {
                        $service->setAgreement((int) $customerId, (int) $key, $pct);
                        $changed++;
                    }
                }
            }
        });

        $this->dispatchBrowserEvent('alert', [
            'type' => $changed ? 'success' : 'info',
            'message' => $changed ? "{$changed} allowance(s) saved!!" : 'Nothing changed.',
        ]);
    }

    public function saveRates()
    {
        $this->validate(['rates.*.*' => 'nullable|numeric|min:0'], [], ['rates.*.*' => 'rate']);

        $changed = 0;
        DB::transaction(function () use (&$changed) {
            foreach ($this->rates as $cargoId => $pair) {
                $cargo = Cargo::find($cargoId);
                if (! $cargo) {
                    continue;
                }
                foreach (['own' => 'own_fleet_chargeable_loss_rate', 'sub' => 'subcontractor_chargeable_loss_rate'] as $key => $field) {
                    $value = is_numeric($pair[$key] ?? null) ? (float) $pair[$key] : null;
                    if (! $this->same($cargo->$field, $value)) {
                        $cargo->$field = $value;
                        $changed++;
                    }
                }
                $cargo->isDirty() && $cargo->save();
            }
        });

        $this->dispatchBrowserEvent('alert', [
            'type' => $changed ? 'success' : 'info',
            'message' => $changed ? "{$changed} rate(s) saved!!" : 'Nothing changed.',
        ]);
    }

    public function export()
    {
        return Excel::download(new LossRatesExport($this->service()->currentCompany()), 'loss-rates-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $service = $this->service();
        $company = $service->currentCompany();

        $data = $this->tab === 'rates'
            ? $this->ratesData($service, $company)
            : $this->allowancesData($service, $company);

        return view('livewire.loss-rates.index', $data + [
            'company' => $company,
            'ownFleetName' => $service->ownFleetName(),
            'cargoTypes' => Cargo::whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
        ]);
    }

    private function allowancesData(LossRateService $service, $company): array
    {
        $cargos = Cargo::query()
            ->when($this->cargoType, fn ($q) => $q->where('type', $this->cargoType))
            ->when(filled($this->cargoSearch), fn ($q) => $q->where('name', 'like', '%' . $this->cargoSearch . '%'))
            ->orderBy('name')->orderBy('id')->get();

        $service->agreements(); // preload for allowance()

        $customers = $service->customersQuery($company)
            ->when(filled($this->search), fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->get();

        // Effective value per cell + gaps per customer.
        $matrix = [];
        foreach ($customers as $customer) {
            $cells = [];
            foreach ($cargos as $cargo) {
                $cells[$cargo->id] = $service->allowance($customer, $cargo, $company);
            }
            $matrix[$customer->id] = [
                'cells' => $cells,
                'gaps' => collect($cells)->whereNull('value')->count(),
            ];
        }

        if ($this->gapsOnly) {
            $customers = $customers->filter(fn ($c) => $matrix[$c->id]['gaps'] > 0)->values();
        }

        $page = $this->page ?? 1;
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $customers->forPage($page, 20)->values(), $customers->count(), 20, $page
        );

        // Seed inputs for what is on screen (once; keeps unsaved typing).
        $agreements = $service->agreements();
        foreach ($cargos as $cargo) {
            if (! array_key_exists($cargo->id, $this->cargoDefaults)) {
                $this->cargoDefaults[$cargo->id] = $this->toUnit($cargo->allowable_loss_percentage);
            }
        }
        foreach ($paginator->items() as $customer) {
            if (! array_key_exists('all', $this->allowances[$customer->id] ?? [])) {
                $this->allowances[$customer->id]['all'] = $this->toUnit($customer->allowable_loss_percentage);
            }
            foreach ($cargos as $cargo) {
                if (! array_key_exists($cargo->id, $this->allowances[$customer->id])) {
                    $this->allowances[$customer->id][$cargo->id] = $this->toUnit($agreements[$customer->id . '|' . $cargo->id] ?? null);
                }
            }
        }

        $totalGaps = collect($matrix)->sum('gaps');
        $customersWithGaps = collect($matrix)->filter(fn ($m) => $m['gaps'] > 0)->count();

        return [
            'cargos' => $cargos,
            'customers' => $paginator,
            'matrix' => $matrix,
            'totalGaps' => $totalGaps,
            'customersWithGaps' => $customersWithGaps,
            'businessDefault' => $company?->allowable_loss_percentage,
        ];
    }

    private function ratesData(LossRateService $service, $company): array
    {
        $cargos = Cargo::query()
            ->when(filled($this->search), fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->cargoType, fn ($q) => $q->where('type', $this->cargoType))
            ->orderBy('name')->orderBy('id')->get();

        $own = new \App\Models\Transporter(['default' => true]);
        $sub = new \App\Models\Transporter(['default' => false]);

        $rows = $cargos->map(fn ($cargo) => [
            'cargo' => $cargo,
            'own' => $service->rate($cargo, $own, $company),
            'sub' => $service->rate($cargo, $sub, $company),
        ]);

        if ($this->gapsOnly) {
            $rows = $rows->filter(fn ($r) => is_null($r['own']['value']) || is_null($r['sub']['value']))->values();
        }

        foreach ($rows as $row) {
            $id = $row['cargo']->id;
            if (! isset($this->rates[$id])) {
                $this->rates[$id] = [
                    'own' => is_numeric($row['cargo']->own_fleet_chargeable_loss_rate) ? $row['cargo']->own_fleet_chargeable_loss_rate + 0 : null,
                    'sub' => is_numeric($row['cargo']->subcontractor_chargeable_loss_rate) ? $row['cargo']->subcontractor_chargeable_loss_rate + 0 : null,
                ];
            }
        }

        return [
            'rateRows' => $rows,
            'rateGaps' => $rows->filter(fn ($r) => is_null($r['own']['value']) || is_null($r['sub']['value']))->count(),
        ];
    }
}
