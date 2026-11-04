<?php

namespace App\Http\Livewire\Trips;

use App\Models\Assignment;
use App\Models\Cargo;
use App\Models\Consignee;
use App\Models\Consignor;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\Horse;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\PaymentMethod;
use App\Models\Trailer;
use App\Models\TrailerAssignment;
use App\Models\TransportOrder;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TripType;
use App\Models\UnitsOfMeasure;
use App\Models\Vendor;
use App\Rules\UniqueReference;
use App\Services\Trips\BulkTripScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Bulk Schedule (Trips > Bulk Schedule): a row per truck, saved as one
 * ordinary pending trip each via BulkTripScheduleService; approval is the
 * usual one on Pending. "Same job" puts every truck on the shared job
 * details; otherwise each row carries its own job (JOB_FIELDS). Trucks can
 * come from any transporter, and transporter freight is set per row.
 */
class BulkCreate extends Component
{
    const STATUSES = ['Scheduled', 'Started', 'Loading Point', 'Loaded', 'InTransit', 'Offloading Point', 'Offloaded', 'OnHold', 'Cancelled'];
    const FREIGHT_CALCULATIONS = [
        'flat_rate' => 'Flat Rate',
        'rate_weight' => 'Rate x Weight / Litreage',
        'rate_distance' => 'Rate x Distance',
        'rate_weight_distance' => 'Rate x Weight / Litreage x Distance',
    ];

    // Held once on the component in "same job" mode, on each row otherwise.
    const JOB_FIELDS = [
        'customer_id', 'trip_type_id', 'cargo_id', 'consignee_id', 'consignor_id', 'currency_id', 'exchange_rate',
        'from', 'to', 'loading_point_id', 'offloading_point_id', 'distance', 'units_of_measure_id',
        'freight_calculation', 'rate', 'cargo_details',
    ];

    public $sameJob = true;

    // Shared job details
    public $customer_id;
    public $trip_type_id;
    public $cargo_id;
    public $consignee_id;
    public $consignor_id;
    public $currency_id;
    public $exchange_rate;
    public $from;
    public $to;
    public $loading_point_id;
    public $offloading_point_id;
    public $distance;
    public $units_of_measure_id;
    public $freight_calculation = 'flat_rate';
    public $rate;
    public $cargo_details;

    // Both modes
    public $trip_status = 'Scheduled';
    public $start_date;
    public $transporter_rate; // default for trucks from subcontracted transporters

    // Per truck: horse_id, label, transporter_id, transporter_name, driver_id, trailer_ids, trip_ref, start_date,
    // weight, quantity, litreage, comments, transporter_agreement, transporter_rate + JOB_FIELDS
    public $rows = [];

    // Trip expenses: key, expense_id, funded_by, category, payment_method_id, currency_id, exchange_rate, vendor_id,
    // amount, all_trips, horse_ids. Each line is added to every trip, or only to the ticked trucks.
    public $expenseLines = [];

    // Truck picker
    public $truckSearch;
    public $pickerTransporterId;
    public $pickedHorses = [];

    public $company;

    protected $listeners = ['openBulkTrips' => 'open'];

    public function mount()
    {
        $this->company = optional(Auth::user()->employee)->company;
    }

    public function open()
    {
        $this->reset(array_merge(self::JOB_FIELDS, ['transporter_rate', 'rows', 'expenseLines', 'truckSearch', 'pickerTransporterId', 'pickedHorses']));
        $this->sameJob = true;
        $this->freight_calculation = 'flat_rate';
        $this->trip_status = 'Scheduled';
        $this->start_date = now()->format('Y-m-d');
        $this->currency_id = optional($this->company)->currency_id;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-bulkTripsModal');
    }

    public function getCargoTypesProperty()
    {
        return Cargo::pluck('type', 'id');
    }

    // The job a row is saved with: the shared one, or the row's own.
    public function job($index): array
    {
        $source = $this->sameJob ? $this->sharedJob() : ($this->rows[$index] ?? []);

        $job = [];
        foreach (self::JOB_FIELDS as $field) {
            $job[$field] = $source[$field] ?? null;
        }
        $job['cargo_type'] = $job['cargo_id'] ? ($this->cargoTypes[$job['cargo_id']] ?? null) : null;

        return $job;
    }

    private function sharedJob(): array
    {
        $job = [];
        foreach (self::JOB_FIELDS as $field) {
            $job[$field] = $this->{$field};
        }

        return $job;
    }

    public function isForeign($currencyId): bool
    {
        return $currencyId && $this->company && $currencyId != $this->company->currency_id;
    }

    public static function usesMeasure($method): bool
    {
        return in_array($method, ['rate_weight', 'rate_weight_distance'], true);
    }

    public static function usesDistance($method): bool
    {
        return in_array($method, ['rate_distance', 'rate_weight_distance'], true);
    }

    // Switching to separate jobs starts every row from the shared details.
    public function updatedSameJob($value)
    {
        if (! $value) {
            foreach ($this->rows as $i => $row) {
                $this->rows[$i] = array_merge($row, $this->sharedJob());
            }
        }
        $this->resetErrorBag();
    }

    public function updatedCurrencyId($id)
    {
        $this->exchange_rate = $this->defaultExchangeRate($id);
    }

    private function defaultExchangeRate($currencyId)
    {
        if (! $this->isForeign($currencyId)) {
            return null;
        }

        return ExchangeRate::where('currency_id', $currencyId)->where('status', 1)->where('expiry', '>', Carbon::today())->value('exchange_rate');
    }

    // The shared start date applies to every row; rows can then be changed one by one.
    public function updatedStartDate($value)
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i]['start_date'] = $value;
        }
    }

    // The default transporter rate updates every row that has transporter freight ticked.
    public function updatedTransporterRate($value)
    {
        foreach ($this->rows as $i => $row) {
            if ($row['transporter_agreement']) {
                $this->rows[$i]['transporter_rate'] = $value;
            }
        }
    }

    public function updatedRows($value, $key)
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, null);

        if ($field === 'trip_ref') {
            $this->checkReferences();
        } elseif ($field === 'currency_id') {
            $this->rows[$index]['exchange_rate'] = $this->defaultExchangeRate($value);
        } elseif ($field === 'transporter_agreement' && $value && ! is_numeric($this->rows[$index]['transporter_rate'] ?? null)) {
            $this->rows[$index]['transporter_rate'] = $this->transporter_rate;
        }
    }

    public function addPickedTrucks()
    {
        $existing = collect($this->rows)->pluck('horse_id')->map(fn ($id) => (int) $id)->all();

        foreach (Horse::with('transporter')->whereIn('id', $this->pickedHorses)->orderByIdentifier()->get() as $horse) {
            if (! in_array($horse->id, $existing, true)) {
                $this->addRow($horse);
            }
        }

        $this->pickedHorses = [];
    }

    // Prefills the truck's active driver and trailer assignments, as picking a horse on Trips > Create does.
    // A separate-jobs row starts as a copy of the previous row's job, so only what differs needs changing.
    private function addRow(Horse $horse): void
    {
        $previous = end($this->rows) ?: null;
        $job = ! $this->sameJob && $previous ? array_intersect_key($previous, array_flip(self::JOB_FIELDS)) : $this->sharedJob();
        $subcontracted = $horse->transporter && ! $horse->transporter->default;

        $this->rows[] = array_merge($job, [
            'horse_id' => $horse->id,
            'label' => $horse->identifier_label,
            'transporter_id' => $horse->transporter_id,
            'transporter_name' => optional($horse->transporter)->name,
            'driver_id' => Assignment::where('horse_id', $horse->id)->where('status', 1)->value('driver_id') ?: '',
            'trailer_ids' => TrailerAssignment::where('horse_id', $horse->id)->where('status', 1)->pluck('trailer_id')->map(fn ($id) => (string) $id)->all(),
            'trip_ref' => '',
            'start_date' => $this->start_date,
            'weight' => '',
            'quantity' => '',
            'litreage' => '',
            'comments' => '',
            'transporter_agreement' => $subcontracted,
            'transporter_rate' => $subcontracted ? $this->transporter_rate : '',
        ]);
    }

    public function copyPrevious($index)
    {
        if ($index < 1 || ! isset($this->rows[$index - 1])) {
            return;
        }

        $this->rows[$index] = array_merge($this->rows[$index], array_intersect_key($this->rows[$index - 1], array_flip(self::JOB_FIELDS)));
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->checkReferences();
    }

    public function addExpenseLine()
    {
        $this->expenseLines[] = [
            'key' => uniqid(),
            'expense_id' => '',
            'funded_by' => '0',
            'category' => 'Self',
            'payment_method_id' => '',
            'currency_id' => optional($this->company)->currency_id,
            'exchange_rate' => null,
            'vendor_id' => '',
            'amount' => '',
            'all_trips' => true,
            'horse_ids' => [],
        ];
    }

    public function removeExpenseLine($index)
    {
        unset($this->expenseLines[$index]);
        $this->expenseLines = array_values($this->expenseLines);
    }

    // Picking an expense fills its defaults the way Trips\Create::mount() does: the master's currency, amount and
    // payment method, and the category/vendor last used for that expense on a trip.
    public function updatedExpenseLines($value, $key)
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, null);

        if ($field === 'expense_id' && $value && ($expense = Expense::find($value))) {
            $last = TripExpense::where('expense_id', $expense->id)->latest('id')->first(['category', 'vendor_id']);
            $this->expenseLines[$index] = array_merge($this->expenseLines[$index], [
                'category' => optional($last)->category ?? $expense->category ?? 'Self',
                'currency_id' => $expense->currency_id ?: optional($this->company)->currency_id,
                'amount' => $expense->amount ?? '',
                'payment_method_id' => $expense->payment_method_id ?? '',
                'vendor_id' => optional($last)->vendor_id ?? '',
            ]);
            $this->expenseLines[$index]['exchange_rate'] = $this->isForeign($this->expenseLines[$index]['currency_id'])
                ? ($expense->exchange_rate ?: $this->defaultExchangeRate($this->expenseLines[$index]['currency_id']))
                : null;
        } elseif ($field === 'currency_id') {
            $this->expenseLines[$index]['exchange_rate'] = $this->defaultExchangeRate($value);
        } elseif ($field === 'funded_by' && $value === '2') {
            $this->expenseLines[$index]['category'] = 'Transporter';
        }
    }

    // The expense lines a truck's trip gets.
    private function expensesFor($horseId): array
    {
        return collect($this->expenseLines)
            ->filter(fn ($line) => $line['all_trips'] || in_array((string) $horseId, array_map('strval', (array) $line['horse_ids']), true))
            ->values()
            ->all();
    }

    private function expenseRules(): array
    {
        $rules = [];
        $horseIds = collect($this->rows)->pluck('horse_id')->map(fn ($id) => (string) $id)->all();

        foreach ($this->expenseLines as $k => $line) {
            $prefix = "expenseLines.$k.";
            $rules[$prefix.'expense_id'] = 'required|exists:expenses,id';
            $rules[$prefix.'funded_by'] = 'required|in:0,1,2';
            $rules[$prefix.'category'] = 'required|in:Self,Customer,Transporter';
            $rules[$prefix.'currency_id'] = 'required|exists:currencies,id';
            $rules[$prefix.'vendor_id'] = 'required|exists:vendors,id';
            $rules[$prefix.'payment_method_id'] = 'nullable|exists:payment_methods,id';
            $rules[$prefix.'amount'] = 'required|numeric|min:0.01';
            if ($this->isForeign($line['currency_id'])) {
                $rules[$prefix.'exchange_rate'] = 'required|numeric|min:0.0001';
            }
            if (! $line['all_trips']) {
                $rules[$prefix.'horse_ids'] = ['required', 'array', function ($attribute, $value, $fail) use ($horseIds) {
                    if (! array_intersect(array_map('strval', (array) $value), $horseIds)) {
                        $fail('Tick at least one truck.');
                    }
                }];
            }
        }

        return $rules;
    }

    private function jobRules(string $prefix, array $job): array
    {
        return [
            $prefix.'customer_id' => 'required|exists:customers,id',
            $prefix.'trip_type_id' => 'required|exists:trip_types,id',
            $prefix.'cargo_id' => 'required|exists:cargos,id',
            $prefix.'currency_id' => 'required|exists:currencies,id',
            $prefix.'exchange_rate' => ($this->isForeign($job['currency_id']) ? 'required' : 'nullable').'|numeric|min:0.0001',
            $prefix.'from' => 'required|exists:destinations,id',
            $prefix.'to' => 'required|exists:destinations,id',
            $prefix.'loading_point_id' => 'nullable|exists:loading_points,id',
            $prefix.'offloading_point_id' => 'nullable|exists:offloading_points,id',
            $prefix.'freight_calculation' => 'required|in:'.implode(',', array_keys(self::FREIGHT_CALCULATIONS)),
            $prefix.'rate' => 'required|numeric|min:0',
            $prefix.'distance' => (static::usesDistance($job['freight_calculation']) ? 'required' : 'nullable').'|numeric|min:0',
        ];
    }

    protected function rules()
    {
        $rules = [
            'trip_status' => 'required|in:'.implode(',', self::STATUSES),
            'rows' => 'required|array|min:1',
            'rows.*.horse_id' => 'required|distinct',
            'rows.*.transporter_id' => 'required|exists:transporters,id',
            'rows.*.driver_id' => 'required|distinct|exists:drivers,id',
            'rows.*.start_date' => 'required|date',
            'rows.*.trip_ref' => 'nullable|string|max:255|distinct:ignore_case',
            'rows.*.weight' => 'nullable|numeric|min:0',
            'rows.*.quantity' => 'nullable|numeric|min:0',
            'rows.*.litreage' => 'nullable|numeric|min:0',
        ];

        if ($this->sameJob) {
            $rules += $this->jobRules('', $this->sharedJob());
        }

        foreach ($this->rows as $i => $row) {
            $job = $this->job($i);
            if (! $this->sameJob) {
                $rules += $this->jobRules("rows.$i.", $job);
            }
            if (static::usesMeasure($job['freight_calculation'])) {
                $rules[$job['cargo_type'] === 'Liquid' ? "rows.$i.litreage" : "rows.$i.weight"] = 'required|numeric|min:0.01';
            }
            if (! empty($row['transporter_agreement'])) {
                $rules["rows.$i.transporter_rate"] = 'required|numeric|min:0';
            }
        }

        return $rules + $this->expenseRules();
    }

    protected $messages = [
        'rows.required' => 'Add at least one truck.',
        'rows.*.horse_id.distinct' => 'This truck is listed twice.',
        'rows.*.driver_id.required' => 'Select the driver.',
        'rows.*.driver_id.distinct' => 'This driver is on another truck.',
        'rows.*.start_date.required' => 'Enter the start date.',
        'rows.*.trip_ref.distinct' => 'This reference is entered on another truck.',
        'rows.*.weight.required' => 'Enter the weight.',
        'rows.*.litreage.required' => 'Enter the litreage.',
        'rows.*.transporter_rate.required' => 'Enter the transporter rate.',
        'rows.*.distance.required' => 'Enter the distance.',
        'rows.*.exchange_rate.required' => 'Enter the conversion rate.',
        'rows.*.*.required' => 'Required.',
        'expenseLines.*.amount.min' => 'Enter the amount.',
        'expenseLines.*.horse_ids.required' => 'Tick at least one truck.',
        'expenseLines.*.*.required' => 'Required.',
        'distance.required' => 'Enter the distance for this freight calculation.',
    ];

    // Trip refs also become the transport order's custom ref, so both must be free (as on Trips > Create).
    private function referenceProblems(): array
    {
        $problems = [];

        foreach ($this->rows as $i => $row) {
            $ref = trim((string) ($row['trip_ref'] ?? ''));
            if (UniqueReference::exists(Trip::class, 'trip_ref', $ref) || UniqueReference::exists(TransportOrder::class, 'custom_ref', $ref)) {
                $problems["rows.$i.trip_ref"] = 'This trip reference is already in use.';
            }
        }

        return $problems;
    }

    private function checkReferences(): void
    {
        foreach (array_keys($this->getErrorBag()->messages()) as $key) {
            if (preg_match('/^rows\.\d+\.trip_ref$/', $key)) {
                $this->resetErrorBag($key);
            }
        }

        foreach ($this->referenceProblems() as $field => $message) {
            $this->addError($field, $message);
        }
    }

    // A trailer can only be hitched to one truck in the batch.
    private function trailerProblems(): array
    {
        $problems = [];
        $seen = [];

        foreach ($this->rows as $i => $row) {
            foreach ((array) ($row['trailer_ids'] ?? []) as $trailerId) {
                if (isset($seen[$trailerId])) {
                    $problems["rows.$i.trailer_ids"] = 'A trailer here is also on '.$this->rows[$seen[$trailerId]]['label'].'.';
                }
                $seen[$trailerId] = $i;
            }
        }

        return $problems;
    }

    public function rowFreight($index)
    {
        $job = $this->job($index);

        return BulkTripScheduleService::freight($job['freight_calculation'], $job['rate'], BulkTripScheduleService::measure($this->rows[$index], $job['cargo_type']), $job['distance']);
    }

    public function rowTransporterFreight($index)
    {
        $job = $this->job($index);
        $row = $this->rows[$index];

        return empty($row['transporter_agreement']) ? 0
            : BulkTripScheduleService::freight($job['freight_calculation'], $row['transporter_rate'], BulkTripScheduleService::measure($row, $job['cargo_type']), $job['distance']);
    }

    public function getTotalFreightProperty()
    {
        return round(collect(array_keys($this->rows))->sum(fn ($i) => $this->rowFreight($i)), 2);
    }

    public function store(BulkTripScheduleService $service)
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i]['trip_ref'] = trim((string) ($row['trip_ref'] ?? ''));
        }

        $this->validate();

        $problems = $this->referenceProblems() + $this->trailerProblems();
        if ($problems) {
            foreach ($problems as $field => $message) {
                $this->addError($field, $message);
            }
            return;
        }

        $trips = [];
        foreach ($this->rows as $i => $row) {
            $job = $this->job($i);
            $job['exchange_rate'] = $this->isForeign($job['currency_id']) ? $job['exchange_rate'] : null;
            $trips[] = array_merge($row, $job, [
                'trip_status' => $this->trip_status,
                'transporter_agreement' => (bool) $row['transporter_agreement'],
                'expenses' => $this->expensesFor($row['horse_id']),
            ]);
        }

        $created = $service->create($trips, $this->company);

        session()->flash('success', $created->count().' trips scheduled ('.$created->first()->trip_number.' to '.$created->last()->trip_number.'). They are awaiting authorization under Pending.');

        return redirect()->route('trips.index');
    }

    // Same availability rule as Trips\Create::updatedSelectedStatus(): in-progress statuses need free, unserviced assets.
    private function assetQuery(string $model)
    {
        return $model::query()
            ->where('archive', 0)
            ->when(! in_array($this->trip_status, BulkTripScheduleService::AVAILABLE_STATUSES, true), function ($q) use ($model) {
                $q->where('status', 1);
                if ($model !== Driver::class) {
                    $q->where('service', 0);
                }
            });
    }

    public function render()
    {
        $taken = collect($this->rows)->pluck('horse_id')->all();

        $trucks = $this->assetQuery(Horse::class)
            ->with('transporter:id,name')
            ->whereNotIn('id', $taken)
            ->when($this->pickerTransporterId, fn ($q) => $q->where('transporter_id', $this->pickerTransporterId))
            ->when(filled($this->truckSearch), function ($q) {
                $term = '%'.$this->truckSearch.'%';
                $q->where(fn ($qq) => $qq->where('registration_number', 'like', $term)->orWhere('fleet_number', 'like', $term));
            })
            ->orderByIdentifier()
            ->limit(100)
            ->get();

        // Each row picks from its own transporter's drivers and trailers. A prefilled one outside
        // the availability filter (e.g. busy) stays selectable so the row still shows it.
        $transporterIds = collect($this->rows)->pluck('transporter_id')->filter()->unique()->all();
        $driverIds = collect($this->rows)->pluck('driver_id')->filter()->all();
        $trailerIds = collect($this->rows)->pluck('trailer_ids')->flatten()->filter()->all();

        $drivers = Driver::with('employee:id,name,surname')
            ->where(fn ($q) => $q->whereIn('id', $this->assetQuery(Driver::class)->whereIn('transporter_id', $transporterIds)->select('id'))->orWhereIn('id', $driverIds))
            ->get()
            ->filter(fn ($driver) => $driver->employee)
            ->sortBy(fn ($driver) => $driver->employee->name.' '.$driver->employee->surname)
            ->groupBy('transporter_id');

        $trailers = Trailer::where(fn ($q) => $q->whereIn('id', $this->assetQuery(Trailer::class)->whereIn('transporter_id', $transporterIds)->select('id'))->orWhereIn('id', $trailerIds))
            ->orderByIdentifier()
            ->get()
            ->groupBy('transporter_id');

        return view('livewire.trips.bulk-create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'transporters' => Transporter::where('authorization', 'approved')->orderBy('name')->get(['id', 'name']),
            'tripTypes' => TripType::orderBy('name')->get(['id', 'name']),
            'cargos' => Cargo::orderBy('name')->get(['id', 'name', 'type']),
            'consignees' => Consignee::orderBy('name')->get(['id', 'name']),
            'consignors' => Consignor::orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::with('country')->get()->sortBy('city')->sortBy('country.name'),
            'loadingPoints' => LoadingPoint::orderBy('name')->get(['id', 'name']),
            'offloadingPoints' => OffloadingPoint::orderBy('name')->get(['id', 'name']),
            'unitsOfMeasures' => UnitsOfMeasure::orderBy('name')->get(['id', 'name']),
            'expenses' => Expense::orderBy('name')->get(['id', 'name']),
            'vendors' => $this->expenseLines ? Vendor::orderBy('name')->get(['id', 'name']) : collect(),
            'paymentMethods' => PaymentMethod::orderBy('name')->get(['id', 'name']),
            'trucks' => $trucks,
            'drivers' => $drivers,
            'trailers' => $trailers,
        ]);
    }
}
