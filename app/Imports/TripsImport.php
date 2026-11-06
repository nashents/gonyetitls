<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;
use App\Imports\Concerns\ChecksFleetLimit;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Deal;
use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Container;
use App\Services\Fuel\FuelTripAllocationService;
use App\Services\Accounting\FuelJournalService;
use App\Services\Accounting\TripExpenseRecoveryService;
use App\Services\Accounting\TransporterPaymentBillService;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\TripType;
use App\Models\TripOrigin;
use App\Models\BillExpense;
use App\Models\Transporter;
use App\Models\TripExpense;
use App\Models\DeliveryNote;
use App\Models\LoadingPoint;
use App\Models\PaymentMethod;
use App\Models\TransportOrder;
use App\Rules\UniqueReference;
use App\Models\OffloadingPoint;
use App\Models\TripDestination;
use App\Models\TripTransportOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TripsImport implements ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use SkipsTemplateExampleRows;

    use Importable, SkipsErrors, ChecksFleetLimit;

    public $company;
    public $trailer_ids;
    public $currency;
    public $payment_account;
    protected $skippedRows = [];
    // Rows imported with something left out (e.g. a fuel order line)
    protected $warnings = [];

    // Ref# (lower-cased) => record already created/updated by an earlier row of this upload
    protected $ordersSeen = [];
    protected $dealsSeen = [];
    protected $fuelsSeen = [];

    // Master data created by this upload, by type
    protected $created = [];

    public function getSkippedRows(): array
    {
        return $this->skippedRows;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    // First filled value among header aliases (our snake_case names or the manifest's headings)
    protected function cell(Collection $row, array $keys)
    {
        foreach ($keys as $key) {
            $value = $row->get($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    // 1530 / "1,530.50" => float, blank/text => null
    protected function toNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = str_replace([',', ' '], '', (string) $value);

        return is_numeric($value) ? (float) $value : null;
    }

    protected function initials(): string
    {
        $words = explode(' ', (string) $this->company?->name);

        return isset($words[1][0]) ? $words[0][0] . $words[1][0] : ($words[0][0] ?? '');
    }

    // ──────────────────────────────────────────────
    // Deal (DealRef# / DealQty): the first row with a DealRef#
    // creates the deal (or updates DealQty on an existing one);
    // every later row with it just attaches.
    // ──────────────────────────────────────────────
    protected function resolveDeal(Collection $row, array $ctx): ?Deal
    {
        $ref = trim((string) $this->cell($row, ['deal_ref', 'dealref', 'deal_reference']));
        if ($ref === '') {
            return null;
        }

        $key = mb_strtolower($ref);
        if (isset($this->dealsSeen[$key])) {
            return $this->dealsSeen[$key];
        }

        $qty = $this->toNumber($this->cell($row, ['deal_qty', 'dealqty', 'deal_quantity']));
        $qtyField = $ctx['cargo_type'] === 'Liquid' ? 'litreage' : ($ctx['cargo_type'] === 'Solid' ? 'weight' : 'quantity');

        $deal = Deal::whereRaw('LOWER(TRIM(reference)) = ?', [$key])->first();

        if (!$deal) {
            $deal = new Deal;
            $deal->user_id = Auth::id();
            $deal->company_id = $this->company->id;
            $deal->customer_id = $ctx['customer']?->id;
            $deal->currency_id = $ctx['currency']?->id;
            $deal->cargo_id = $ctx['cargo']?->id;
            $deal->start_date = $ctx['start_date'];
            $deal->end_date = $ctx['end_date'];
            $last = Deal::orderBy('id', 'desc')->value('id') ?? 0;
            $deal->deal_number = $this->initials() . 'D' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            $deal->reference = $ref;
            $deal->status = 1;
        }

        if ($qty !== null) {
            $deal->$qtyField = $qty;
        }
        $deal->save();

        return $this->dealsSeen[$key] = $deal;
    }

    // ──────────────────────────────────────────────
    // Fuel orders. FuelOrder# may list several orders
    // ("FO1, FO2") with the figure columns matched by position.
    // ──────────────────────────────────────────────
    protected function importFuelOrders(Trip $trip, Collection $row, string $category, $trip_ref): void
    {
        // Template headings: fuel_order_ref / _qty / _amt / _retail_amt (rates = amount / qty)
        $numbers = $this->splitList($this->cell($row, ['fuel_order_ref', 'foref', 'fuel_order_number', 'fuel_order', 'fuelorder']));
        if (!$numbers) {
            return;
        }

        $count = count($numbers);
        $column = fn (array $keys) => $this->splitAmounts($this->cell($row, $keys), $count);
        $qtys = $column(['foqty', 'fuel_order_qty', 'fuelorder_qty', 'fuel_order_quantity']);
        $rates = $column(['forate', 'fuel_order_rate', 'fuelorder_rate']);
        $retailRates = $column(['foretailrate', 'fuel_order_retail_rate', 'fuelorder_retail_rate']);
        $amounts = $column(['foamt', 'fuel_order_amount', 'fuel_order_amt', 'fuelorderamt', 'fuelorder_amt']);
        $retailAmounts = $column(['foretailamt', 'fuel_order_retail_amount', 'fuel_order_retail_amt', 'fuelorder_retail_amt']);

        $station = $this->resolveStation(
            $this->cell($row, ['fuel_station', 'station']),
            $trip->currency_id ? Currency::find($trip->currency_id) : $this->currency
        );

        foreach ($numbers as $i => $number) {
            $qty = $qtys[$i] ?? null;
            $rate = $rates[$i] ?? null;
            $amount = $amounts[$i] ?? null;

            if (!$qty && $rate && $amount) {
                $qty = round($amount / $rate, 2);
            }
            if (!$rate && $qty && $amount) {
                $rate = round($amount / $qty, 4);
            }
            if (!$amount && $qty && $rate) {
                $amount = round($qty * $rate, 2);
            }
            if (!$qty || $qty <= 0 || !$rate) {
                $this->warnings[] = ['ref' => $trip_ref, 'reason' => "fuel order {$number} needs a quantity and a rate or amount - not imported"];
                continue;
            }

            $retailRate = $retailRates[$i] ?? null;
            if (!$retailRate && !empty($retailAmounts[$i])) {
                $retailRate = round($retailAmounts[$i] / $qty, 4);
            }

            $this->importFuelOrder($trip, $number, (float) $qty, (float) $rate, (float) $amount, $retailRate, $category, $station, $trip_ref);
        }
    }

    /**
     * The first row naming a fuel order creates it (or overwrites one no trip
     * uses yet); later rows - or an order already on trips - add their trip
     * as another allocation and grow the order by their litres and amount.
     */
    protected function importFuelOrder(Trip $trip, string $number, float $qty, float $rate, float $amount, $retailRate, string $category, $station, $trip_ref): void
    {
        $allocations = app(FuelTripAllocationService::class);
        $key = mb_strtolower($number);

        $fuel = $this->fuelsSeen[$key] ?? Fuel::where(function ($q) use ($key) {
            $q->whereRaw('LOWER(TRIM(reference)) = ?', [$key])
              ->orWhereRaw('LOWER(TRIM(order_number)) = ?', [$key]);
        })->first();

        $rows = $fuel ? $allocations->rowsFor($fuel) : [];
        $adding = !empty($rows);
        $rows[] = ['trip_id' => $trip->id, 'quantity' => $qty];
        $newQty = $adding ? round((float) $fuel->quantity + $qty, 2) : $qty;

        if ($error = $allocations->validationError($rows, $newQty, (bool) $fuel?->supplied_by_customer, $fuel?->horse_id ?: $trip->horse_id)) {
            $this->warnings[] = ['ref' => $trip_ref, 'reason' => "fuel order {$number}: {$error}"];
            return;
        }

        if (!$fuel) {
            $fuel = new Fuel;
            $fuel->user_id = Auth::id();
            $last = Fuel::withTrashed()->max('id') ?? 0;
            $fuel->order_number = $this->initials() . 'FO' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            $fuel->reference = $number;
            $fuel->authorization = 'pending';
            $fuel->supplied_by_customer = false;
            $fuel->is_full_tank = false;
            $fuel->comments = 'Imported historical fuel order';
        }

        if ($adding) {
            $fuel->amount = round((float) $fuel->amount + $amount, 2);
            $fuel->quantity = $newQty;
            $fuel->unit_price = round($fuel->amount / $newQty, 4);
        } else {
            $fuel->quantity = $qty;
            $fuel->unit_price = $rate;
            $fuel->amount = $amount;
            $fuel->currency_id = $trip->currency_id ?: $this->company->currency_id;
            $fuel->horse_id = $trip->horse_id;
            $fuel->vehicle_id = $trip->horse_id ? null : $trip->vehicle_id;
            $fuel->type = $trip->horse_id ? 'Horse' : ($trip->vehicle_id ? 'Vehicle' : ($fuel->type ?: 'Horse'));
            $fuel->driver_id = $trip->driver_id;
            $fuel->employee_id = $trip->driver?->employee_id;
            $fuel->date = $trip->start_date ? $trip->start_date->format('Y-m-d') : now()->toDateString();
            $fuel->container_id = $station?->id ?? $fuel->container_id;
        }
        $fuel->deduct_from = $fuel->deduct_from ?: 'quantity';
        $fuel->fuel_type = $fuel->fuel_type ?: 'Diesel';
        if ($fuel->currency_id == $this->company->currency_id) {
            $fuel->exchange_rate = null;
            $fuel->exchange_amount = null;
        }
        $fuel->save();

        // Allocations: trip expenses (Fuel - COGS) split by litres, cost of sales refreshed
        $allocations->sync($fuel, $rows, $category);

        // Historical order: approved now that it is on its trip(s)
        if ($fuel->authorization !== 'approved') {
            $fuel->authorization = 'approved';
            $fuel->authorized_by_id = Auth::id();
            $fuel->authorization_date = now();
            $fuel->reason = 'Imported historical fuel order';
            $fuel->save();
        }

        // Retail = what the transporter is charged; used as the markup when posting
        $fuel->retailPriceOverride = $retailRate ? (float) $retailRate : null;
        app(FuelJournalService::class)->postConsumptionSafely($fuel);

        // Own-fleet fuel isn't recharged, but keep the retail figures for the manifest
        if ($retailRate && !app(TripExpenseRecoveryService::class)->isTransporterFuel($fuel)) {
            $fuel->transporter_price = round((float) $retailRate, 4);
            $fuel->transporter_total = round((float) $retailRate * (float) $fuel->quantity, 2);
            $fuel->saveQuietly();
        }

        $this->fuelsSeen[$key] = $fuel;
    }

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
        $this->currency = $this->company->currency;

        // Account from which imported expense payments are made
        $this->payment_account = Account::where('name', 'Trip Expense')->first();
    }

    public function tripNumber()
    {
        if ($this->company) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0] . $words[1][0];
            } else {
                $initials = $words[0][0];
            }
        }

        $trip = Trip::orderBy('id', 'desc')->first();

        if (!$trip) {
            $trip_number = $initials . 'T' . str_pad(1, 5, "0", STR_PAD_LEFT);
        } else {
            $number = $trip->id + 1;
            $trip_number = $initials . 'T' . str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return $trip_number;
    }

    public function transportOrderNumber()
    {
        if ($this->company) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0] . $words[1][0];
            } else {
                $initials = $words[0][0];
            }
        }

        $transport_order = TransportOrder::orderBy('id', 'desc')->first();

        if (!$transport_order) {
            $transport_order_number = $initials . 'TO' . str_pad(1, 5, "0", STR_PAD_LEFT);
        } else {
            $number = $transport_order->id + 1;
            $transport_order_number = $initials . 'TO' . str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return $transport_order_number;
    }

    public function ttoNumber()
    {
        if ($this->company) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0] . $words[1][0];
            } else {
                $initials = $words[0][0];
            }
        }

        $tto = TripTransportOrder::orderBy('id', 'desc')->first();

        if (!$tto) {
            $tto_number = $initials . 'TTO' . str_pad(1, 5, "0", STR_PAD_LEFT);
        } else {
            $number = $tto->id + 1;
            $tto_number = $initials . 'TTO' . str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return $tto_number;
    }

    public function billNumber()
    {
        if ($this->company) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0] . $words[1][0];
            } else {
                $initials = $words[0][0];
            }
        }

        $bill = Bill::orderBy('id', 'desc')->first();

        if (!$bill) {
            $bill_number = $initials . 'B' . str_pad(1, 5, "0", STR_PAD_LEFT);
        } else {
            $number = $bill->id + 1;
            $bill_number = $initials . 'B' . str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return $bill_number;
    }

    public function paymentNumber()
    {
        if ($this->company) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0] . $words[1][0];
            } else {
                $initials = $words[0][0];
            }
        }

        $payment = Payment::orderBy('id', 'desc')->first();

        if (!$payment) {
            $payment_number = $initials . 'P' . str_pad(1, 5, "0", STR_PAD_LEFT);
        } else {
            $number = $payment->id + 1;
            $payment_number = $initials . 'P' . str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return $payment_number;
    }

    private function parseExcelDate($value)
    {
        if (!isset($value)) {
            return null;
        }

        // 1️⃣ Numeric Excel serial
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                );
            } catch (\Exception $e) {
                return null;
            }
        }

        // 2️⃣ If it's a string, normalize separators
        if (is_string($value)) {
            $value = trim($value);
            $normalized = preg_replace('/[\.\/\\\\]/', '-', $value);

            try {
                return Carbon::createFromFormat('Y-m-d', $normalized);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────
    // Auto-derive freight from rate/measurement/distance
    // when it isn't provided manually in the excel file
    // ──────────────────────────────────────────────
    protected function calculateFreight($method, $rate, $measurement_value, $distance)
    {
        if (!is_numeric($rate)) {
            return null;
        }

        switch ($method) {
            case 'flat_rate':
                return (float) $rate;
            case 'rate_weight':
                return is_numeric($measurement_value) ? (float) $rate * (float) $measurement_value : null;
            case 'rate_weight_distance':
                return (is_numeric($measurement_value) && is_numeric($distance))
                    ? (float) $rate * (float) $measurement_value * (float) $distance
                    : null;
            case 'rate_distance':
                return is_numeric($distance) ? (float) $rate * (float) $distance : null;
            default:
                return null;
        }
    }

    // ──────────────────────────────────────────────
    // Master data: find ignoring spaces + case, create if missing
    // ──────────────────────────────────────────────
    protected $lookupCache = [];

    // "ADK 4533" / "adk4533" => "adk4533"
    protected function normalise($value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', '', (string) $value));
    }

    /**
     * Match on $columns ignoring spaces and case: an exact match first, then a
     * "contains" match only when it is the one record that contains it (so
     * "ADK453" never silently picks "ADK4533" over "ADK4534").
     */
    protected function findNormalised(string $modelClass, $value, array $columns = ['name'])
    {
        $norm = $this->normalise($value);
        if ($norm === '') {
            return null;
        }

        // spaces, tabs and non-breaking spaces (pasted from Excel) all ignored
        $expr = fn ($column) => "REPLACE(REPLACE(REPLACE(LOWER(`{$column}`), ' ', ''), CHAR(9 USING utf8mb4), ''), CHAR(194, 160 USING utf8mb4), '')";

        $exact = $modelClass::where(function ($q) use ($columns, $expr, $norm) {
            foreach ($columns as $column) {
                $q->orWhereRaw($expr($column) . ' = ?', [$norm]);
            }
        })->orderBy('id')->first();
        if ($exact) {
            return $exact;
        }

        $like = $modelClass::where(function ($q) use ($columns, $expr, $norm) {
            foreach ($columns as $column) {
                $q->orWhereRaw($expr($column) . ' LIKE ?', ['%' . addcslashes($norm, '%_\\') . '%']);
            }
        })->limit(2)->get();

        return $like->count() === 1 ? $like->first() : null;
    }

    /**
     * Find by name (spaces/case ignored) or create with $create attributes.
     * An existing record only has its blank $fill attributes filled in.
     */
    protected function resolveOrCreate(string $modelClass, $name, array $create = [], array $fill = [])
    {
        $name = is_string($name) ? trim(preg_replace('/\s+/u', ' ', $name)) : $name;

        if (!$name) {
            return null;
        }

        $cacheKey = $modelClass . '|' . $this->normalise($name);

        if (isset($this->lookupCache[$cacheKey])) {
            return $this->lookupCache[$cacheKey];
        }

        $record = $this->findNormalised($modelClass, $name);

        if (!$record) {
            $record = new $modelClass;
            $columns = $this->columnsOf($record->getTable());
            $record->forceFill(array_intersect_key(
                array_merge(['name' => $name, 'user_id' => Auth::id()], $create, $fill),
                array_flip($columns)
            ));
            $record->save();
            $this->created[class_basename($modelClass)][] = $name;
        } else {
            $this->fillBlanks($record, $fill);
        }

        return $this->lookupCache[$cacheKey] = $record;
    }

    protected $columnCache = [];

    protected function columnsOf(string $table): array
    {
        return $this->columnCache[$table] ??= \Illuminate\Support\Facades\Schema::getColumnListing($table);
    }

    // Set only attributes that are empty on the record (never overwrite)
    protected function fillBlanks($record, array $values): void
    {
        foreach ($values as $key => $value) {
            if (($record->$key === null || $record->$key === '') && $value !== null && $value !== '') {
                $record->$key = $value;
            }
        }
        if ($record->isDirty()) {
            $record->save();
        }
    }

    // <company initials><prefix><next id>, the numbering every master screen uses
    protected function nextNumber(string $modelClass, string $prefix): string
    {
        $query = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass))
            ? $modelClass::withTrashed() : $modelClass::query();

        return $this->initials() . $prefix . str_pad(((int) $query->max('id')) + 1, 5, '0', STR_PAD_LEFT);
    }

    protected function resolveTransporter($name)
    {
        return $this->resolveOrCreate(Transporter::class, $name, [
            'user_id'            => null, // a transporter's user is its own login
            'creator_id'         => Auth::id(),
            'transporter_number' => $this->nextNumber(Transporter::class, 'T'),
            'authorization'      => 'approved',
            'status'             => 1,
        ], [
            'company_id' => $this->company->id,
        ]);
    }

    protected function resolveCustomer($name)
    {
        return $this->resolveOrCreate(Customer::class, $name, [
            'user_id'         => null, // a customer's user is its own login
            'creator_id'      => Auth::id(),
            'customer_number' => $this->nextNumber(Customer::class, 'V'),
            'status'          => 1,
        ], [
            'company_id' => $this->company->id,
        ]);
    }

    // Currencies are matched on code or full name; an unknown one is never created
    protected function resolveCurrency($value, $trip_ref)
    {
        if (!filled($value)) {
            return $this->currency;
        }

        $key = Currency::class . '|' . $this->normalise($value);
        if (!array_key_exists($key, $this->lookupCache)) {
            $this->lookupCache[$key] = $this->findNormalised(Currency::class, $value, ['name', 'fullname', 'symbol']);
        }
        if (!$this->lookupCache[$key]) {
            $this->warnings[] = ['ref' => $trip_ref, 'reason' => "currency \"{$value}\" not found - company currency used"];
        }

        return $this->lookupCache[$key] ?: $this->currency;
    }

    /**
     * Horse by registration (spaces/case ignored), created under the row's
     * transporter when missing; an existing horse with no transporter gets it.
     */
    protected function resolveHorse($registration, $transporter, $trip_ref)
    {
        $registration = trim(preg_replace('/\s+/u', ' ', (string) $registration));
        if ($registration === '') {
            return null;
        }

        $key = Horse::class . '|' . $this->normalise($registration);
        if (array_key_exists($key, $this->lookupCache)) {
            return $this->lookupCache[$key];
        }

        $horse = $this->findNormalised(Horse::class, $registration, ['registration_number']);

        if (!$horse) {
            if (!$this->fleetRoom('horse', $transporter, $registration, $trip_ref)) {
                return $this->lookupCache[$key] = null;
            }
            $horse = new Horse;
            $horse->user_id = Auth::id();
            $horse->horse_number = $this->nextNumber(Horse::class, 'H');
            $horse->registration_number = $registration;
            $horse->transporter_id = $transporter?->id;
            $horse->status = 1;
            $horse->save();
            $this->fleetAdded($transporter);
            $this->created['Horse'][] = $registration;
        } else {
            $this->fillBlanks($horse, ['transporter_id' => $transporter?->id]);
        }

        return $this->lookupCache[$key] = $horse;
    }

    protected function resolveTrailer($registration, $transporter, $trip_ref)
    {
        $registration = trim(preg_replace('/\s+/u', ' ', (string) $registration));
        if ($registration === '') {
            return null;
        }

        $key = Trailer::class . '|' . $this->normalise($registration);
        if (array_key_exists($key, $this->lookupCache)) {
            return $this->lookupCache[$key];
        }

        $trailer = $this->findNormalised(Trailer::class, $registration, ['registration_number']);

        if (!$trailer) {
            if (!$this->fleetRoom('trailer', $transporter, $registration, $trip_ref)) {
                return $this->lookupCache[$key] = null;
            }
            $trailer = new Trailer;
            $trailer->user_id = Auth::id();
            $trailer->trailer_number = $this->nextNumber(Trailer::class, 'T');
            $trailer->registration_number = $registration;
            $trailer->transporter_id = $transporter?->id;
            $trailer->status = 1;
            $trailer->save();
            $this->fleetAdded($transporter);
            $this->created['Trailer'][] = $registration;
        } else {
            $this->fillBlanks($trailer, ['transporter_id' => $transporter?->id]);
        }

        return $this->lookupCache[$key] = $trailer;
    }

    /**
     * Driver by full name (spaces/case ignored, middle name optional). A new
     * one is an Employee + Driver record under the row's transporter - no
     * login is created (add one from Drivers when needed).
     */
    protected function resolveDriver($fullName, $transporter)
    {
        $fullName = trim(preg_replace('/\s+/u', ' ', (string) $fullName));
        if ($fullName === '') {
            return null;
        }

        $key = Driver::class . '|' . $this->normalise($fullName);
        if (array_key_exists($key, $this->lookupCache)) {
            return $this->lookupCache[$key];
        }

        $norm = $this->normalise($fullName);
        $strip = fn ($sql) => "REPLACE(LOWER({$sql}), ' ', '')";
        $employees = Employee::whereRaw($strip("CONCAT(COALESCE(name,''), COALESCE(surname,''))") . ' = ?', [$norm])
            ->orWhereRaw($strip("CONCAT(COALESCE(name,''), COALESCE(middle_name,''), COALESCE(surname,''))") . ' = ?', [$norm])
            ->orWhereRaw($strip("CONCAT(COALESCE(surname,''), COALESCE(name,''))") . ' = ?', [$norm])
            ->orderBy('id')->get();
        // Prefer the employee who is already a driver
        $employee = $employees->first(fn ($e) => $e->driver) ?? $employees->first();

        $parts = explode(' ', $fullName);
        $surname = count($parts) > 1 ? array_pop($parts) : '';
        $name = implode(' ', $parts);

        if (!$employee) {
            $employee = new Employee;
            $employee->company_id = $this->company->id;
            $employee->creator_id = Auth::id();
            $employee->employee_number = $this->nextNumber(Employee::class, 'E');
            $employee->name = $name;
            $employee->surname = $surname;
            $employee->post = 'Driver';
            $employee->status = 1;
            $employee->save();
            $this->created['Driver'][] = $fullName;
        }

        $driver = $employee->driver;
        if (!$driver) {
            $driver = new Driver;
            $driver->company_id = $employee->company_id ?: $this->company->id;
            $driver->creator_id = Auth::id();
            $driver->user_id = $employee->user_id;
            $driver->employee_id = $employee->id;
            $driver->driver_number = $this->nextNumber(Driver::class, 'D');
            $driver->transporter_id = $transporter?->id;
            $driver->status = 1;
            $driver->save();
        } else {
            $this->fillBlanks($driver, ['transporter_id' => $transporter?->id]);
        }

        return $this->lookupCache[$key] = $driver;
    }

    // Fuel station by name; a missing one is created as a Once Off Buy station
    protected function resolveStation($name, $currency)
    {
        return $this->resolveOrCreate(Container::class, $name, [
            'container_number' => $this->nextNumber(Container::class, 'FS'),
            'purchase_type'    => 'Once Off Buy',
            'fuel_type'        => 'Diesel',
        ], [
            'currency_id' => $currency?->id,
        ]);
    }

    // Business Settings fleet limit (same check as the Horses/Trailers imports)
    protected function fleetRoom(string $type, $transporter, $registration, $trip_ref): bool
    {
        if ($this->canAddToFleet($this->company, $type, $transporter)) {
            return true;
        }
        $this->warnings[] = ['ref' => $trip_ref, 'reason' => "{$type} {$registration} not created - fleet limit reached"];

        return false;
    }

    protected function fleetAdded($transporter): void
    {
        $this->registerFleetAddition($this->company, $transporter);
    }

    // What the upload created, by type: ['Horse' => ['ADK4533', ...], ...]
    public function getCreated(): array
    {
        return array_map(fn ($names) => array_values(array_unique($names)), $this->created);
    }

    public function limit(): int
    {
        return 2500;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            if ($row->filter()->isNotEmpty()) {

                // Clean whitespace
                $row = $row->map(fn($v) => is_string($v) ? trim($v) : $v);

                // Skip Excel lookup rows (Trip Status / Trip Type dropdown source)
                $nonEmpty = $row->filter(fn($v) => $v !== null && $v !== '')->count();

                if ($nonEmpty <= 2) {
                    continue;
                }

                if (!$row->get('horse_registration_number') && !$row->get('customer')) {
                    continue;
                }

                // One row = one trip with its order, deal, fuel and bills: all or nothing
                DB::transaction(fn () => $this->importRow($row));
            }
        }
    }

    protected function importRow(Collection $row): void
    {
                $trip_number = $this->tripNumber();
                $user_id = Auth::id();
                $company_id = $this->company->id;
                $trailer_ids = [];

                $trip_ref = trim((string) $row->get('trip_reference'));
                $order_ref = trim((string) $this->cell($row, ['order_ref', 'orderref', 'order_reference']));

                // Without an OrderRef# the trip ref becomes the order's custom ref (as before)
                if (UniqueReference::exists(Trip::class, 'trip_ref', $trip_ref)
                    || ($order_ref === '' && UniqueReference::exists(TransportOrder::class, 'custom_ref', $trip_ref))) {
                    $this->skippedRows[] = ['ref' => $trip_ref, 'reason' => 'trip reference already in use'];
                    return;
                }
                $start_date = $this->parseExcelDate($row->get('start_date'));
                $end_date = $this->parseExcelDate($row->get('offloading_date'));

                // Master data: found ignoring spaces/case, created when missing.
                // Transporter first - new horses/trailers/drivers go under it.
                $transporter = $this->resolveTransporter($row->get('transporter'));

                $driver = $this->resolveDriver($row->get('driver'), $transporter);
                $horse = $this->resolveHorse($row->get('horse_registration_number'), $transporter, $trip_ref);
                $vehicle = $horse?->vehicle;

                $customer = $this->resolveCustomer($row->get('customer'));

                $cargo_type = trim((string) $row->get('cargo_type')) ?: null;

                $cargo = $this->resolveOrCreate(Cargo::class, $row->get('cargo'), [
                    'type' => $cargo_type ?: 'Solid', // default for auto-created cargo
                ]);

                // Row-level cargo_type drives the freight calculation; fall back to the Cargo record's type
                $cargo_type = $cargo_type ?: $cargo?->type;

                // Own fleet (default transporter, or none): fuel + expenses are "Self".
                // Any other transporter: they are "Transporter" (recovered from them).
                $own_fleet = !$transporter || $transporter->default;
                $expense_category = $own_fleet ? 'Self' : 'Transporter';

                $trip_type = $this->resolveOrCreate(TripType::class, $row->get('trip_type'));

                // Fallback to company currency if blank/unknown
                $currency = $this->resolveCurrency($row->get('currency'), $trip_ref);

                // Trailer IDs (created under the row's transporter when missing)
                $trailer_ids = [];
                $trailer_regs = array_map(
                    'trim',
                    preg_split('/[,\/;|]+/', (string) $row->get('trailer_reg_numbers'), -1, PREG_SPLIT_NO_EMPTY)
                );
                foreach ($trailer_regs as $reg) {
                    $trailer = $this->resolveTrailer($reg, $transporter, $trip_ref);
                    if ($trailer) {
                        $trailer_ids[] = $trailer->id;
                    }
                }
                $trailer_ids = array_values(array_unique($trailer_ids));

                $freight_calculation = trim((string) $row->get('freight_calculation_method')) ?: null;
                if (!in_array($freight_calculation, ['flat_rate', 'rate_weight', 'rate_weight_distance', 'rate_distance'])) {
                    $freight_calculation = $row->get('rate') ? 'flat_rate' : null;
                }

                if ($cargo_type === "Solid") {
                    $calculation_measurement = "weight";
                } elseif ($cargo_type === "Liquid") {
                    $calculation_measurement = "litreage_at_ambient";
                } else {
                    $calculation_measurement = null;
                }

                $distance = $row->get('distance');

                // ──────────────────────────────────────────────
                // Loading / offloading points (one or many per trip).
                // Each figure column holds a matching list by position, e.g.
                // loading_points "Mine A, Mine B" + weight "20, 14".
                // ──────────────────────────────────────────────
                $origins = $this->buildLegs(
                    LoadingPoint::class,
                    $row->get('loading_points') ?? $row->get('loading_point'),
                    $row,
                    self::LOADED_COLUMNS
                );

                $destinations = $this->buildLegs(
                    OffloadingPoint::class,
                    $row->get('offloading_points') ?? $row->get('offloading_point'),
                    $row,
                    self::OFFLOADED_COLUMNS
                );

                $loaded = $this->legTotals($origins);
                $offloaded = $this->legTotals($destinations);

                // The trip header runs from the first loading point to the final offloading point
                $loading_point = $origins[0]['point'];
                $offloading_point = end($destinations)['point'];
                $multiple_points = count($origins) > 1 || count($destinations) > 1;

                // weight for Solid cargo, litreage_at_ambient for Liquid cargo
                $measurement_value = $this->legMeasurement($loaded, $cargo_type);

                $rate = $row->get('rate');
                $freight = $row->get('freight');

                // Only auto-calculate freight when it hasn't been set manually in the excel file
                if (!is_numeric($freight)) {
                    $freight = $this->calculateFreight($freight_calculation, $rate, $measurement_value, $distance);
                }

                // Subcontracted trips: what we owe the transporter
                $transporter_rate = $own_fleet ? null : $this->toNumber($row->get('transporter_rate'));
                $transporter_freight = $own_fleet ? null : $this->toNumber($row->get('transporter_freight'));
                if (!$own_fleet && $transporter_freight === null && $transporter_rate !== null) {
                    $transporter_freight = $this->calculateFreight($freight_calculation, $transporter_rate, $measurement_value, $distance);
                }
                $transporter_agreement = !$own_fleet && $transporter_freight > 0;

                $deal = $this->resolveDeal($row, [
                    'customer'   => $customer,
                    'cargo'      => $cargo,
                    'cargo_type' => $cargo_type,
                    'currency'   => $currency,
                    'start_date' => $start_date,
                    'end_date'   => $end_date,
                ]);

                $trip = new Trip();
                $trip->trip_number = $trip_number;
                $trip->user_id = $user_id;
                $trip->company_id = $company_id;
                $trip->trip_ref = $trip_ref;
                $trip->trip_type_id = $trip_type?->id;
                $trip->transporter_id = $transporter?->id;
                $trip->horse_id = $horse?->id;
                $trip->driver_id = $driver?->id;
                $trip->vehicle_id = $vehicle?->id;
                $trip->customer_id = $customer?->id;
                $trip->currency_id = $currency?->id;
                $trip->loading_point_id = $loading_point?->id;
                $trip->offloading_point_id = $offloading_point?->id;
                $trip->cargo_id = $cargo?->id;
                $trip->start_date = $start_date;
                $trip->end_date = $end_date;
                $trip->trip_status = trim($row->get('trip_status'));
                $trip->rate = $rate;
                $trip->freight = $freight;
                $trip->weight = $loaded['weight'];
                $trip->quantity = $loaded['quantity'];
                $trip->litreage = $loaded['litreage'];
                $trip->litreage_at_20 = $loaded['litreage_at_20'];
                $trip->offloaded_quantity = $offloaded['quantity'];
                $trip->multiple_destinations = $multiple_points;
                $trip->distance = $distance;
                $trip->deal_id = $deal?->id;
                $trip->transporter_agreement = $transporter_agreement;
                $trip->transporter_rate = $transporter_rate;
                $trip->transporter_freight = $transporter_freight;

                $trip->with_trailer = count($trailer_ids) > 0 ? 1 : 0;
                $trip->with_customer_rates = "custom";
                $trip->with_transporter_rates = "custom";
                $trip->freight_calculation = $freight_calculation;
                $trip->calculation_measurement = $calculation_measurement;

                $trip->turnover = $trip->freight;

                // Authorization (old trips => auto-approved)
                $trip->authorization = "approved";
                $trip->authorized_by_id = $user_id;
                $trip->authorization_date = now();
                $trip->reason = "Imported historical trip";

                $trip->save();

                if (!empty($trailer_ids)) {
                    $trip->trailers()->sync($trailer_ids);
                }

                // ──────────────────────────────────────────────
                // Transport Order: OrderRef# finds/creates it; later rows
                // with the same OrderRef# attach their trip to it.
                // ──────────────────────────────────────────────
                $lookups = [
                    'customer'                => $customer,
                    'cargo'                   => $cargo,
                    'trip_type'               => $trip_type,
                    'currency'                => $currency,
                    'loading_point'           => $loading_point,
                    'offloading_point'        => $offloading_point,
                    'freight_calculation'     => $freight_calculation,
                    'calculation_measurement' => $calculation_measurement,
                    'start_date'              => $start_date,
                    'end_date'                => $end_date,
                    'trip_ref'                => $trip_ref,
                    'rate'                    => $rate,
                    'freight'                 => $freight,
                    'distance'                => $distance,
                    'cargo_type'              => $cargo_type,
                    'origins'                 => $origins,
                    'destinations'            => $destinations,
                    'loaded'                  => $loaded,
                    'multiple_points'         => $multiple_points,
                    'order_ref'               => $order_ref,
                    'deal'                    => $deal,
                    'transporter_rate'        => $transporter_rate,
                    'transporter_freight'     => $transporter_freight,
                ];

                [$transport_order, $attached] = $this->resolveTransportOrder($row, $lookups);

                // Link Trip <-> Transport Order with this row's share
                $trip_transport_order = $this->createTripTransportOrder($trip, $transport_order, [
                    'quantity' => $loaded['quantity'],
                    'weight'   => $loaded['weight'],
                    'litreage' => $loaded['litreage'],
                    'freight'  => $freight,
                    'rate'     => $rate,
                    'deal_id'  => $deal?->id ?? $transport_order->deal_id,
                ]);

                // Loading/offloading legs for this trip (an attached order keeps the earlier trips' legs)
                $this->addOrigins($transport_order, $row, $lookups, !$attached, $trip, $trip_transport_order);
                $this->addDestinations($transport_order, $row, $lookups, !$attached, $trip, $trip_transport_order);

                // Authorize all transport orders linked to this trip
                $this->authorizeTransportOrders($trip);

                // ──────────────────────────────────────────────
                // Trip Expenses
                // ──────────────────────────────────────────────
                $expenseColumn = $row->get('expenses');
                $imported_expense_ids = [];

                if ($expenseColumn && $trip) {

                    $expenseColumn = preg_replace('/\s*,\s*/', ',', $expenseColumn);
                    $expensePairs = explode(',', $expenseColumn);

                    foreach ($expensePairs as $pair) {

                        if (trim($pair) === '') {
                            continue;
                        }

                        $pair = preg_replace('/\s*:\s*/', ':', $pair);

                        [$name, $amount] = array_pad(explode(':', $pair), 2, null);

                        $name   = $name ? trim($name) : null;
                        $amount = $amount ? trim($amount) : null;

                        if (!$name || !is_numeric($amount)) {
                            continue;
                        }

                        $amount = (float) $amount;

                        $expense = Expense::firstOrCreate(
                            ['name' => $name],
                            [
                                'status' => 1,
                                'account_id' => 30,
                                'type' => 'Direct',
                                'user_id' => Auth::user()->id,
                            ]
                        );

                        $tripExpense = TripExpense::firstOrCreate(
                            [
                                'trip_id'    => $trip->id,
                                'expense_id' => $expense->id,
                            ],
                            [
                                'amount' => 0,
                                'currency_id' => $this->currency->id,
                                'user_id' => Auth::user()->id,
                                'category' => $expense_category,
                                'payment_method_id' => PaymentMethod::where('name', 'Cash')->first()?->id,
                            ]
                        );

                        $tripExpense->increment('amount', $amount);
                        $imported_expense_ids[] = $tripExpense->id;
                    }

                    // Create approved Bill + BillExpense + Payment per trip expense
                    foreach (TripExpense::whereIn('id', array_unique($imported_expense_ids))->get() as $trip_expense) {
                        $bill = $this->createTripExpenseBill($trip, $trip_expense);
                        if ($bill) {
                            $this->recordImportPayment($bill, $trip_expense);
                        }
                    }
                }

                // ──────────────────────────────────────────────
                // Fuel orders (FuelOrder# ...): created or updated by
                // their number, allocated to this trip and posted
                // ──────────────────────────────────────────────
                $this->importFuelOrders($trip, $row, $expense_category, $trip_ref);

                // ──────────────────────────────────────────────
                // Transporter Payment (subcontracted trips): the bill
                // owed to the transporter, posted once Offloaded
                // ──────────────────────────────────────────────
                if ($transporter_agreement) {
                    $foreign = $currency && $currency->id != $this->company->currency_id;

                    $payment_expense = new TripExpense;
                    $payment_expense->user_id = $user_id;
                    $payment_expense->trip_id = $trip->id;
                    $payment_expense->transporter_id = $transporter->id;
                    $payment_expense->expense_id = Expense::where('name', 'Transporter Payment')->value('id');
                    $payment_expense->currency_id = $currency?->id;
                    $payment_expense->category = 'Self';
                    $payment_expense->amount = $transporter_freight;
                    $payment_expense->exchange_rate = $foreign ? null : 1;
                    $payment_expense->exchange_amount = $foreign ? null : $transporter_freight;
                    $payment_expense->date = $start_date;
                    $payment_expense->save();

                    $warning = null;
                    app(TransporterPaymentBillService::class)->syncTrip($trip->fresh(), $warning);
                    if ($warning) {
                        $this->warnings[] = ['ref' => $trip_ref, 'reason' => $warning];
                    }
                }

                // ──────────────────────────────────────────────
                // Delivery Note (linked to the TTO)
                // ──────────────────────────────────────────────
                $delivery_note = new DeliveryNote();
                $delivery_note->user_id = $user_id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->transport_order_id = $transport_order->id;
                $delivery_note->trip_transport_order_id = $trip_transport_order?->id;
                $delivery_note->loaded_date = $trip->start_date;
                $delivery_note->offloaded_date = $trip->end_date;
                $delivery_note->loaded_litreage = $trip->litreage;
                $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_quantity = $trip->quantity;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->offloaded_weight = $offloaded['weight'];
                $delivery_note->offloaded_quantity = $offloaded['quantity'];
                $delivery_note->offloaded_litreage = $offloaded['litreage'];
                $delivery_note->offloaded_litreage_at_20 = $offloaded['litreage_at_20'];
                $delivery_note->save();

                // Update statuses if trip is completed or cancelled
                if (in_array($trip->trip_status, ['Offloaded', 'Cancelled', 'Scheduled'])) {
                    if ($horse) $horse->update(['status' => 1]);
                    if ($vehicle) $vehicle->update(['status' => 1]);
                    if ($driver) $driver->update(['status' => 1]);

                    foreach ($trip->trailers as $trailer) {
                        $trailer?->update(['status' => 1]);
                    }

                    foreach ($trip->breakdown_assignments as $ba) {
                        Horse::withTrashed()->find($ba->horse_id)?->update(['status' => 1]);
                        Vehicle::withTrashed()->find($ba->vehicle_id)?->update(['status' => 1]);
                        Driver::withTrashed()->find($ba->driver_id)?->update(['status' => 1]);

                        foreach ($ba->trailers as $t) {
                            Trailer::withTrashed()->find($t->id)?->update(['status' => 1]);
                        }
                    }
                }

                // Cost of sales = the trip's "Self" expenses in company currency
                // (Transporter lines are recovered from the transporter)
                $trip->refresh();
                $cost_of_sales = TripExpense::where('trip_id', $trip->id)->where('category', 'Self')->get()
                    ->sum(function (TripExpense $expense) {
                        $value = $expense->currency_id == $this->company->currency_id ? $expense->amount : $expense->exchange_amount;

                        return is_numeric($value) ? (float) $value : 0;
                    });
                $trip->cost_of_sales = $cost_of_sales;
                $turnover = $trip->turnover;

                if ((is_numeric($cost_of_sales) && $cost_of_sales) > 0 && (is_numeric($turnover) && $turnover > 0)) {
                    $net_profit = $turnover - $cost_of_sales;
                    $trip->net_profit = $net_profit;

                    if (is_numeric($net_profit) && $net_profit > 0) {
                        $trip->markup_percentage = ($net_profit / $cost_of_sales) * 100;
                        $trip->net_profit_percentage = ($net_profit / $turnover) * 100;
                    } else {
                        $trip->markup_percentage = 0;
                        $trip->net_profit_percentage = 0;
                    }
                } else {
                    $trip->net_profit_percentage = 100;
                    $trip->markup_percentage = 100;
                }

                $trip->update();
    }

    // ──────────────────────────────────────────────
    // Authorize all Transport Orders on a trip
    // ──────────────────────────────────────────────
    public function authorizeTransportOrders($trip)
    {
        $ttos = $trip->trip_transport_orders;

        if ($ttos) {
            foreach ($ttos as $tto) {
                $to = $tto->transport_order;
                if ($to) {
                    $to->authorized_by_id = Auth::user()->id;
                    $to->authorization = "approved";
                    $to->authorization_date = now();
                    $to->comments = "Imported historical trip";
                    $to->update();
                }
            }
        }
    }

    // ──────────────────────────────────────────────
    // Bill + BillExpense per Trip Expense (approved)
    // ──────────────────────────────────────────────
    public function createTripExpenseBill($trip, $trip_expense)
    {
        // Skip if a bill already exists for this trip expense
        if (Bill::where('trip_expense_id', $trip_expense->id)->exists()) {
            return null;
        }

        // Non-billable expenses stay a trip cost only - no Bill.
        if ($trip_expense->expense && ! $trip_expense->expense->is_locked && ! ($trip_expense->expense->billable ?? true)) {
            return null;
        }

        // The expense's/allowance's own account; "Trip Expense" only as a fallback.
        // Transporter / Customer category lines are recoverable, not company costs.
        $account = app(\App\Services\Accounting\TripExpenseRecoveryService::class)->recoverableAccountFor($trip_expense)
            ?: optional($trip_expense->expense)->account
            ?: optional($trip_expense->allowance)->account
            ?: $this->payment_account;

        $bill = new Bill;
        $bill->user_id = Auth::user()->id;
        $bill->bill_number = $this->billNumber();
        $bill->trip_id = $trip->id ?: null;
        $bill->fuel_id = $trip_expense->fuel_id ?: null;
        $bill->trip_expense_id = $trip_expense->id ?: null;
        $bill->horse_id = $trip->horse_id ?: null;
        $bill->vehicle_id = $trip->vehicle_id ?: null;
        if (isset($account)) {
            $bill->account_id = $account->id ?: null;
            $bill->account_type_id = $account->account_type->id ?: null;
        }
        $bill->driver_id = $trip->driver_id ?: null;
        $bill->category = "Trip Expense";
        $bill->bill_date = $trip->start_date ? $trip->start_date->format('Y-m-d') : date("Y-m-d");
        $bill->currency_id = $trip_expense->currency_id ?: null;
        $bill->subtotal = $trip_expense->amount;
        $bill->total = $trip_expense->amount;
        if ($trip_expense->currency_id != $this->company->currency_id) {
            $bill->exchange_rate = $trip_expense->exchange_rate;
            $bill->exchange_amount = $trip_expense->exchange_amount;
        }
        $bill->balance = $trip_expense->amount;
        $bill->authorized_by_id = Auth::user()->id;
        // Stay "pending" until the expense line below exists, so the
        // BillObserver doesn't post a journal entry with no debit line.
        $bill->authorization = "pending";
        $bill->comments = "Imported historical trip expense";
        $bill->save();

        $bill_expense = new BillExpense;
        $bill_expense->user_id = Auth::user()->id;
        $bill_expense->bill_id = $bill->id ?: null;
        $bill_expense->currency_id = $bill->currency_id ?: null;
        $bill_expense->expense_id = $trip_expense->expense_id ?: null;
        $bill_expense->allowance_id = $trip_expense->allowance_id ?: null;
        if (isset($account)) {
            $bill_expense->account_id = $account->id ?: null;
            $bill_expense->account_type_id = $account->account_type->id ?: null;
        }
        $bill_expense->qty = 1;
        if ($trip_expense->currency_id != $this->company->currency_id) {
            $bill_expense->exchange_rate = $trip_expense->exchange_rate;
            $bill_expense->exchange_amount = $trip_expense->exchange_amount;
        }
        $bill_expense->amount = $trip_expense->amount;
        $bill_expense->subtotal = $trip_expense->amount;
        $bill_expense->subtotal_incl = $trip_expense->amount;
        $bill_expense->save();

        // Approve now that the expense line exists — this is what
        // triggers BillObserver -> BillJournalService::post().
        $bill->authorization = "approved";
        $bill->authorization_date = now();
        $bill->save();

        return $bill;
    }

    // ──────────────────────────────────────────────
    // Record full payment for an imported bill
    // ──────────────────────────────────────────────
    public function recordImportPayment($bill, $trip_expense)
    {
        DB::transaction(function () use ($bill, $trip_expense) {

            $account = $this->payment_account;
            $amount = (float) $bill->total;
            $payment_date = $bill->bill_date;

            $payment = new Payment;
            $payment->company_id = $this->company->id;
            $payment->vendor_id = $bill->vendor_id;
            $payment->transporter_id = $bill->transporter_id;
            $payment->container_id = $bill->container_id;
            $payment->top_up_id = $bill->top_up_id;
            $payment->trip_id = $bill->trip_id;
            $payment->bill_id = $bill->id;
            $payment->movement = "Dbt";
            $payment->description = $bill->notes;
            $payment->user_id = Auth::user()->id;
            $payment->currency_id = $bill->currency_id;
            $payment->payment_number = $this->paymentNumber();
            $payment->notes = "Imported historical trip expense payment";
            $payment->category = "Bill";
            $payment->mode_of_payment = "Cash";
            $payment->account_id = $account?->id;
            $payment->amount = $amount;
            $payment->exchange_rate = $bill->exchange_rate;
            $payment->exchange_amount = $bill->exchange_amount;
            $payment->balance = 0; // paid in full
            $payment->date = $payment_date;
            $payment->save();

            // Deduct from the paying account's floating balance
            if ($account && is_numeric($account->balance)) {
                $account->balance = $account->balance - $amount;
                $account->update();
            }

            // Mark bill as fully paid
            $bill->balance = 0;
            $bill->status = "Paid";
            $bill->update();
        });
    }

    // ──────────────────────────────────────────────
    // Transport Order creation (import version)
    // ──────────────────────────────────────────────
    /**
     * [order, attached]. Blank OrderRef# => a new order per trip (custom ref =
     * trip ref, as before). Otherwise the first row with that OrderRef#
     * creates the order - or overwrites an existing one no trip uses yet -
     * and every later row (or an order already on trips) attaches its trip,
     * adding its figures to the order's totals.
     */
    protected function resolveTransportOrder(Collection $row, array $lookups): array
    {
        $ref = $lookups['order_ref'];

        if ($ref === '') {
            return [$this->createTransportOrder(null, $row, $lookups), false];
        }

        $key = mb_strtolower($ref);
        $order = $this->ordersSeen[$key]
            ?? TransportOrder::whereRaw('LOWER(TRIM(custom_ref)) = ?', [$key])->first();

        if ($order && (isset($this->ordersSeen[$key]) || TripTransportOrder::where('transport_order_id', $order->id)->exists())) {
            foreach (['quantity', 'weight', 'litreage', 'litreage_at_20'] as $field) {
                if ($lookups['loaded'][$field] !== null) {
                    $order->$field = round((float) $order->$field + (float) $lookups['loaded'][$field], 2);
                }
            }
            foreach (['freight' => 'freight', 'transporter_freight' => 'transporter_freight'] as $field => $key2) {
                if (is_numeric($lookups[$key2])) {
                    $order->$field = round((float) $order->$field + (float) $lookups[$key2], 2);
                }
            }
            $order->deal_id = $order->deal_id ?: $lookups['deal']?->id;
            $order->save();

            return [$this->ordersSeen[$key] = $order, true];
        }

        return [$this->ordersSeen[$key] = $this->createTransportOrder($order, $row, $lookups), false];
    }

    public function createTransportOrder($existing, $row, array $lookups)
    {
        $transport_order = $existing ?: new TransportOrder;
        $transport_order->transport_order_number = $transport_order->transport_order_number ?: $this->transportOrderNumber();
        $transport_order->custom_ref = ($lookups['order_ref'] ?? '') !== '' ? $lookups['order_ref'] : $lookups['trip_ref'];
        $transport_order->bill_of_entry = $row->get('bill_of_entry') ?: null;
        $transport_order->user_id = $transport_order->user_id ?: Auth::id();
        $transport_order->deal_id = $lookups['deal']?->id;
        $transport_order->company_id = $this->company->id ?: null;
        $transport_order->quotation_id = null;
        $transport_order->with_customer_rates = "custom";
        $transport_order->with_transporter_rates = "custom";
        $transport_order->customer_id = $lookups['customer']?->id;
        $transport_order->consignee_id = null;
        $transport_order->freight_calculation = $lookups['freight_calculation'];
        $transport_order->calculation_measurement = $lookups['calculation_measurement'];
        $transport_order->currency_id = $lookups['currency']?->id;
        $transport_order->cargo_id = $lookups['cargo']?->id;
        $transport_order->trip_type_id = $lookups['trip_type']?->id;
        $transport_order->defined_customer_rate_id = null;
        $transport_order->defined_transporter_rate_id = null;
        $transport_order->from = null;
        $transport_order->to = null;
        $transport_order->offloading_point_id = $lookups['offloading_point']?->id;
        $transport_order->loading_point_id = $lookups['loading_point']?->id;
        $transport_order->start_date = $lookups['start_date'];
        $transport_order->end_date = $lookups['end_date'];
        $transport_order->cargo_details = $row->get('cargo_details') ?: null;
        $transport_order->multiple_destinations = $lookups['multiple_points'];
        $transport_order->quantity = $lookups['loaded']['quantity'];
        $transport_order->litreage = $lookups['loaded']['litreage'];
        $transport_order->litreage_at_20 = $lookups['loaded']['litreage_at_20'];
        $transport_order->units_of_measure_id = null;
        $transport_order->weight = $lookups['loaded']['weight'];
        $transport_order->status = "Pending";
        $transport_order->rate = $lookups['rate'] ?: null;
        $transport_order->freight = $lookups['freight'] ?: null;
        $transport_order->transporter_rate = $lookups['transporter_rate'] ?? null;
        $transport_order->transporter_freight = $lookups['transporter_freight'] ?? null;
        $transport_order->exchange_rate = null;
        $transport_order->exchange_customer_freight = null;
        $transport_order->distance = $lookups['distance'] ?: null;
        $transport_order->save();

        return $transport_order;
    }

    public function addDestinations($transport_order, $row, array $lookups, bool $replace = true, $trip = null, $trip_transport_order = null)
    {
        if ($replace) {
            TripDestination::where('transport_order_id', $transport_order->id)->delete();
        }

        $single = count($lookups['destinations']) === 1;

        foreach ($lookups['destinations'] as $leg) {
            // A single drop with no offloaded figures takes the loaded totals (as before)
            if ($single) {
                foreach ($leg['figures'] as $field => $value) {
                    $leg['figures'][$field] = $value ?? $lookups['loaded'][$field];
                }
            }

            TripDestination::create([
                'transport_order_id'  => $transport_order->id,
                'trip_id'             => $trip?->id,
                'trip_transport_order_id' => $trip_transport_order?->id,
                'destination_id'      => $transport_order->to,
                'offloading_point_id' => $leg['point']?->id,
                'user_id'             => Auth::id(),
                'weight'              => $leg['figures']['weight'],
                'quantity'            => $leg['figures']['quantity'],
                'units_of_measure_id' => null,
                'litreage'            => $leg['figures']['litreage'],
                'litreage_at_20'      => $leg['figures']['litreage_at_20'],
                'rate'                => $lookups['rate'] ?: null,
                'freight'             => $single ? ($lookups['freight'] ?: null) : $this->legFreight($leg, $lookups),
            ]);
        }
    }

    public function addOrigins($transport_order, $row, array $lookups, bool $replace = true, $trip = null, $trip_transport_order = null)
    {
        if ($replace) {
            TripOrigin::where('transport_order_id', $transport_order->id)->delete();
        }

        $single = count($lookups['origins']) === 1;

        foreach ($lookups['origins'] as $leg) {
            TripOrigin::create([
                'transport_order_id'  => $transport_order->id,
                'trip_id'             => $trip?->id,
                'trip_transport_order_id' => $trip_transport_order?->id,
                'destination_id'      => $transport_order->from,
                'loading_point_id'    => $leg['point']?->id,
                'user_id'             => Auth::id(),
                'weight'              => $leg['figures']['weight'],
                'quantity'            => $leg['figures']['quantity'],
                'units_of_measure_id' => null,
                'litreage'            => $leg['figures']['litreage'],
                'litreage_at_20'      => $leg['figures']['litreage_at_20'],
                'rate'                => $lookups['rate'] ?: null,
                'freight'             => $single ? ($lookups['freight'] ?: null) : $this->legFreight($leg, $lookups),
            ]);
        }
    }

    // ──────────────────────────────────────────────
    // Multiple loading / offloading points
    // ──────────────────────────────────────────────

    // Leg field => template column
    const LOADED_COLUMNS = [
        'weight'         => 'weight',
        'quantity'       => 'quantity',
        'litreage'       => 'litreage_at_ambient',
        'litreage_at_20' => 'litreage_at_20',
    ];

    const OFFLOADED_COLUMNS = [
        'weight'         => 'offloaded_weight',
        'quantity'       => 'offloaded_quantity',
        'litreage'       => 'offloaded_litreage_at_ambient',
        'litreage_at_20' => 'offloaded_litreage_at_20',
    ];

    /**
     * One leg per listed point, each carrying the figure at the same position
     * in every figure column. A row with no point still yields one leg so the
     * trip keeps its totals.
     */
    protected function buildLegs(string $modelClass, $points, Collection $row, array $columns): array
    {
        $names = $this->splitList($points);
        $count = max(1, count($names));

        $figures = [];
        foreach ($columns as $field => $column) {
            $figures[$field] = $this->splitAmounts($row->get($column), $count);
        }

        $legs = [];
        for ($i = 0; $i < $count; $i++) {
            $legs[] = [
                'point'   => $this->resolveOrCreate($modelClass, $names[$i] ?? null),
                'figures' => array_map(fn($values) => $values[$i] ?? null, $figures),
            ];
        }

        return $legs;
    }

    // Sum each figure across legs; null when no leg has it
    protected function legTotals(array $legs): array
    {
        $totals = [];
        foreach (array_keys(self::LOADED_COLUMNS) as $field) {
            $values = array_filter(array_column(array_column($legs, 'figures'), $field), fn($v) => $v !== null);
            $totals[$field] = $values ? array_sum($values) : null;
        }

        return $totals;
    }

    protected function legMeasurement(array $figures, $cargo_type)
    {
        return $cargo_type === "Solid"
            ? $figures['weight']
            : ($cargo_type === "Liquid" ? $figures['litreage'] : null);
    }

    // Per-point freight on a multi-point trip; a flat rate belongs to the whole trip
    protected function legFreight(array $leg, array $lookups)
    {
        if ($lookups['freight_calculation'] === 'flat_rate') {
            return null;
        }

        return $this->calculateFreight(
            $lookups['freight_calculation'],
            $lookups['rate'],
            $this->legMeasurement($leg['figures'], $lookups['cargo_type']),
            $lookups['distance']
        );
    }

    // "Mine A, Mine B" => ['Mine A', 'Mine B']
    protected function splitList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/[,;|\r\n]+/', (string) $value)),
            fn($v) => $v !== ''
        ));
    }

    /**
     * "20, 14" => [20.0, 14.0]. With a single point a thousands-formatted
     * text figure like "30,000" stays one value.
     */
    protected function splitAmounts($value, int $points): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_numeric($value)) {
            return [(float) $value];
        }

        $value = trim(str_replace(' ', '', (string) $value));

        if ($points === 1 && preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $value)) {
            return [(float) str_replace(',', '', $value)];
        }

        return array_map(fn($v) => is_numeric($v) ? (float) $v : null, preg_split('/\r\n|[,;|\r\n]/', $value));
    }

    // ──────────────────────────────────────────────
    // Trip <-> Transport Order link (import version)
    // ──────────────────────────────────────────────
    // $allocation: this trip's quantity/weight/litreage/freight/rate/deal_id on the order
    public function createTripTransportOrder($trip, $transport_order, array $allocation)
    {
        $trip_transport_order = TripTransportOrder::firstOrNew([
            'trip_id'            => $trip->id,
            'transport_order_id' => $transport_order->id,
        ]);

        $trip_transport_order->tto_number          = $this->ttoNumber();
        $trip_transport_order->allocated_quantity  = $allocation['quantity'];
        $trip_transport_order->allocated_weight    = $allocation['weight'];
        $trip_transport_order->allocated_litreage  = $allocation['litreage'];
        $trip_transport_order->allocated_freight   = $allocation['freight'];
        $trip_transport_order->deal_id             = $allocation['deal_id'];
        $trip_transport_order->allocated_rate      = $allocation['rate'];
        $trip_transport_order->units_of_measure_id = $transport_order->units_of_measure_id ?: null;
        $trip_transport_order->currency_id         = $transport_order->currency_id;
        $trip_transport_order->exchange_rate       = $transport_order->exchange_rate;
        $trip_transport_order->exchange_amount     = $transport_order->exchange_customer_freight;
        $trip_transport_order->bill_of_entry       = $transport_order->bill_of_entry;
        $trip_transport_order->save();

        // Mark transport order as allocated to a trip
        $transport_order->update(['status' => 'Allocated']);

        return $trip_transport_order;
    }

    public function rules(): array
    {
        return [
            // '*.transporter_id' => ['required'],
        ];
    }

    public function batchSize(): int
    {
        return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
