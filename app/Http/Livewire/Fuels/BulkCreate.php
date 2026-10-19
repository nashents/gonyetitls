<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Container;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\ExchangeRate;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\TopUp;
use App\Models\Trip;
use App\Services\Fuel\BulkFuelOrderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Bulk Create Orders (Fuel Orders > Bulk Actions): one station, one date and
 * price, a row per truck. Saves one ordinary pending fuel order per truck.
 */
class BulkCreate extends Component
{
    // Shared
    public $container_id;
    public $date;
    public $currency_id;
    public $exchange_rate;
    public $unit_price;
    public $deduct_from = 'quantity';
    public $fuel_type = 'Diesel';

    // Per truck: horse_id, label, employee_id, trip_id, category, mileage, quantity, is_full_tank, fillup, comments, tank_capacity
    public $rows = [];
    public $rowTrips = [];

    // Truck picker
    public $truckSearch;
    public $pickedHorses = [];

    public $company;

    protected $listeners = ['openBulkCreate' => 'open'];

    public function mount()
    {
        $this->company = optional(Auth::user()->employee)->company;
    }

    public function open()
    {
        $this->reset(['container_id', 'currency_id', 'exchange_rate', 'unit_price', 'rows', 'rowTrips', 'truckSearch', 'pickedHorses']);
        $this->deduct_from = 'quantity';
        $this->fuel_type = 'Diesel';
        $this->date = now()->format('Y-m-d\TH:i');
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-bulkFuelModal');
    }

    public function getContainerProperty()
    {
        return $this->container_id ? Container::find($this->container_id) : null;
    }

    public function getIsBulkBuyProperty()
    {
        return optional($this->container)->purchase_type == 'Bulk Buy';
    }

    public function getIsForeignCurrencyProperty()
    {
        return $this->currency_id && $this->company && $this->currency_id != $this->company->currency_id;
    }

    // Station sets the currency, fuel type and default price, the same way the single order form does.
    public function updatedContainerId($id)
    {
        $container = $id ? Container::find($id) : null;
        if (! $container) {
            return;
        }

        $rates = TopUp::where('container_id', $container->id)->where('currency_id', $container->currency_id)
            ->where('rate', 'REGEXP', '^[0-9]+(\\.[0-9]+)?$')->pluck('rate');
        if ($rates->count() > 0 && $rates->sum() > 0) {
            $this->unit_price = round($rates->sum() / $rates->count(), 2);
        }

        $this->fuel_type = $container->fuel_type ? ucfirst(strtolower($container->fuel_type)) : 'Diesel';
        $this->currency_id = $container->currency_id;
        $this->updatedCurrencyId($this->currency_id);
    }

    public function updatedCurrencyId($id)
    {
        if (! $id || ! $this->company || $id == $this->company->currency_id) {
            $this->exchange_rate = null;
            return;
        }

        $rate = ExchangeRate::where('currency_id', $id)->where('status', 1)->where('expiry', '>', Carbon::today())->value('exchange_rate');
        if ($rate) {
            $this->exchange_rate = $rate;
        }
    }

    public function addPickedTrucks()
    {
        $existing = collect($this->rows)->pluck('horse_id')->map(fn ($id) => (int) $id)->all();

        foreach (Horse::whereIn('id', $this->pickedHorses)->orderByIdentifier()->get() as $horse) {
            if (! in_array($horse->id, $existing, true)) {
                $this->addRow($horse);
            }
        }

        $this->pickedHorses = [];
    }

    private function addRow(Horse $horse): void
    {
        $trips = Trip::where('authorization', 'approved')->where('trip_status', '!=', 'Cancelled')
            ->where('horse_id', $horse->id)->orderByDesc('created_at')->take(20)->get();

        $index = count($this->rows);
        $this->rowTrips[$index] = $trips->map(fn ($trip) => [
            'id' => $trip->id,
            'label' => $trip->trip_number.($trip->trip_ref ? '/'.$trip->trip_ref : '').' ('.$trip->trip_status.')',
        ])->all();

        $this->rows[$index] = [
            'horse_id' => $horse->id,
            'label' => $horse->identifier_label,
            'employee_id' => $this->defaultEmployeeId($horse),
            'trip_id' => '',
            'category' => 'Self',
            'mileage' => $horse->mileage,
            'quantity' => '',
            'is_full_tank' => '0',
            'fillup' => '1',
            'comments' => '',
            'tank_capacity' => $horse->fuel_tank_capacity,
        ];
    }

    // Truck's current driver assignment, else the driver on its latest approved trip.
    // (Horse::driver() is a stale hasOne - drivers has no horse_id - so it can't be used.)
    private function defaultEmployeeId(Horse $horse)
    {
        $assigned = optional(optional($horse->currentAssignment)->driver)->employee_id;
        if ($assigned) {
            return $assigned;
        }

        $tripDriverId = Trip::where('horse_id', $horse->id)->where('authorization', 'approved')
            ->whereNotNull('driver_id')->orderByDesc('created_at')->value('driver_id');

        return $tripDriverId ? Driver::whereKey($tripDriverId)->value('employee_id') : null;
    }

    public function removeRow($index)
    {
        unset($this->rows[$index], $this->rowTrips[$index]);
        $this->rows = array_values($this->rows);
        $this->rowTrips = array_values($this->rowTrips);
    }

    // "0.trip_id" etc. A trip brings its driver, and it's a top-up if the trip already has its initial fill.
    public function updatedRows($value, $key)
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, null);

        if ($field === 'trip_id' && $value) {
            $trip = Trip::with('driver')->find($value);
            if ($trip && optional($trip->driver)->employee_id) {
                $this->rows[$index]['employee_id'] = $trip->driver->employee_id;
            }
            $this->rows[$index]['fillup'] = Fuel::where('trip_id', $value)->where('fillup', 1)->exists() ? '0' : '1';
        }
    }

    protected function rules()
    {
        $rules = [
            'container_id' => 'required|exists:containers,id',
            'date' => 'required',
            'currency_id' => 'required|exists:currencies,id',
            'unit_price' => 'required|numeric|min:0.01',
            'rows' => 'required|array|min:1',
            'rows.*.horse_id' => 'required|distinct',
            'rows.*.employee_id' => 'required|exists:employees,id',
            'rows.*.mileage' => 'required|numeric|min:0',
            'rows.*.quantity' => 'required|numeric|min:0.01',
            'rows.*.is_full_tank' => 'required|in:0,1',
            'rows.*.fillup' => 'required|in:0,1',
            'rows.*.category' => 'nullable|in:Self,Transporter,Customer',
        ];

        if ($this->isBulkBuy) {
            $rules['deduct_from'] = 'required|in:account,quantity';
        }
        if ($this->isForeignCurrency) {
            $rules['exchange_rate'] = 'required|numeric|min:0.0001';
        }

        return $rules;
    }

    protected $messages = [
        'rows.required' => 'Add at least one truck.',
        'rows.*.employee_id.required' => 'Select the driver.',
        'rows.*.mileage.required' => 'Enter the mileage.',
        'rows.*.quantity.required' => 'Enter the litres.',
        'rows.*.quantity.min' => 'Enter the litres.',
        'rows.*.horse_id.distinct' => 'This truck is listed twice.',
    ];

    // Tank capacity per truck, and the station's balance for the whole batch.
    private function balanceProblems(): array
    {
        $problems = [];

        foreach ($this->rows as $i => $row) {
            if ((float) ($row['tank_capacity'] ?? 0) > 0 && (float) $row['quantity'] > (float) $row['tank_capacity']) {
                $problems["rows.$i.quantity"] = 'More than the tank holds ('.(float) $row['tank_capacity'].' l).';
            }
        }

        if ($this->isBulkBuy) {
            if ($this->deduct_from === 'quantity' && $this->totalQuantity > (float) $this->container->balance) {
                $problems['batch'] = 'Total litres ('.$this->totalQuantity.' l) exceed the station\'s quantity balance ('.(float) $this->container->balance.' l).';
            }
            if ($this->deduct_from === 'account' && $this->totalAmount > (float) $this->container->account_balance) {
                $problems['batch'] = 'Total amount ('.number_format($this->totalAmount, 2).') exceeds the station\'s account balance ('.number_format((float) $this->container->account_balance, 2).').';
            }
        }

        return $problems;
    }

    public function getTotalQuantityProperty()
    {
        return round(collect($this->rows)->sum(fn ($row) => (float) ($row['quantity'] ?? 0)), 2);
    }

    public function getTotalAmountProperty()
    {
        return round(collect($this->rows)->sum(fn ($row) => round((float) ($row['quantity'] ?? 0) * (float) $this->unit_price, 2)), 2);
    }

    public function store(BulkFuelOrderService $service)
    {
        $this->validate();

        $problems = $this->balanceProblems();
        if ($problems) {
            foreach ($problems as $field => $message) {
                $this->addError($field, $message);
            }
            return;
        }

        $fuels = $service->create([
            'container_id' => $this->container_id,
            'date' => $this->date,
            'currency_id' => $this->currency_id,
            'unit_price' => $this->unit_price,
            'exchange_rate' => $this->isForeignCurrency ? $this->exchange_rate : null,
            'deduct_from' => $this->isBulkBuy ? $this->deduct_from : 'quantity',
            'fuel_type' => $this->fuel_type,
        ], $this->rows, $this->company);

        session()->flash('success', $fuels->count().' fuel orders created ('.$fuels->first()->order_number.' to '.$fuels->last()->order_number.'). They are awaiting authorization under Pending.');

        return redirect()->route('fuels.index');
    }

    public function render()
    {
        $taken = collect($this->rows)->pluck('horse_id')->all();

        $trucks = Horse::where('status', 1)->where('archive', 0)
            ->whereNotIn('id', $taken)
            ->when(filled($this->truckSearch), function ($q) {
                $term = '%'.$this->truckSearch.'%';
                $q->where(fn ($qq) => $qq->where('registration_number', 'like', $term)->orWhere('fleet_number', 'like', $term));
            })
            ->orderByIdentifier()
            ->limit(100)
            ->get();

        // Drivers first; any prefilled employee who isn't a driver stays selectable.
        $employeeIds = collect($this->rows)->pluck('employee_id')->filter()->all();
        $employees = Employee::where(fn ($q) => $q->whereHas('driver')->orWhereIn('id', $employeeIds))
            ->orderBy('name')->orderBy('surname')->get(['id', 'name', 'surname']);

        return view('livewire.fuels.bulk-create', [
            'containers' => Container::orderBy('name')->get(),
            'currencies' => Currency::orderBy('name')->get(),
            'trucks' => $trucks,
            'employees' => $employees,
        ]);
    }
}
