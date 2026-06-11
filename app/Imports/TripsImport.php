<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Trip;
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
    use Importable, SkipsErrors;

    public $company;
    public $trailer_ids;
    public $currency;
    public $payment_account;

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
    // Find by fuzzy name match, create if missing
    // ──────────────────────────────────────────────
    protected $lookupCache = [];

    protected function resolveOrCreate(string $modelClass, $name, array $extra = [])
    {
        $name = is_string($name) ? trim($name) : $name;

        if (!$name) {
            return null;
        }

        $cacheKey = $modelClass . '|' . strtolower($name);

        if (isset($this->lookupCache[$cacheKey])) {
            return $this->lookupCache[$cacheKey];
        }

        // 1️⃣ Fuzzy match first (same behaviour as before)
        $record = $modelClass::where('name', 'LIKE', '%' . $name . '%')->first();

        // 2️⃣ Create if not found
        if (!$record) {
            $record = $modelClass::create(array_merge([
                'name'    => $name,
                'user_id' => Auth::id(),
            ], $extra));
        }

        return $this->lookupCache[$cacheKey] = $record;
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

                $trip_number = $this->tripNumber();
                $user_id = Auth::id();
                $company_id = $this->company->id;
                $trailer_ids = [];

                $trip_ref = trim($row->get('trip_reference'));
                $start_date = $this->parseExcelDate($row->get('start_date'));
                $end_date = $this->parseExcelDate($row->get('offloading_date'));

                $driver_name = trim($row->get('driver'));
                $driver = null;

                if ($driver_name) {
                    $name_parts = explode(' ', $driver_name);
                    if (count($name_parts) >= 2) {
                        $name = $name_parts[0];
                        $surname = $name_parts[1] ?? $name_parts[2] ?? null;
                        if ($surname) {
                            $employee = Employee::where('name', 'LIKE', "%$name%")
                                ->where('surname', 'LIKE', "%$surname%")
                                ->first();
                            $driver = $employee?->driver;
                        }
                    }
                }

                $horse = Horse::where('registration_number', 'LIKE', '%' . trim($row->get('horse_registration_number')) . '%')->first();
                $vehicle = $horse?->vehicle;

                $customer = $this->resolveOrCreate(Customer::class, $row->get('customer'), [
                    'company_id' => $company_id,
                    'status'     => 1,
                ]);

                $loading_point = $this->resolveOrCreate(LoadingPoint::class, $row->get('loading_point'));

                $offloading_point = $this->resolveOrCreate(OffloadingPoint::class, $row->get('offloading_point'));

                $cargo = $this->resolveOrCreate(Cargo::class, $row->get('cargo'), [
                    'type' => 'Solid', // default for auto-created cargo
                ]);

                $transporter = $this->resolveOrCreate(Transporter::class, $row->get('transporter'), [
                    'company_id' => $company_id,
                    'status'     => 1,
                ]);

                $trip_type = $this->resolveOrCreate(TripType::class, $row->get('trip_type'));

                // Fallback to company currency if blank/unknown
                $currency = $this->resolveOrCreate(Currency::class, $row->get('currency')) ?? $this->currency;

                // Trailer IDs
                $trailer_ids = [];
                $trailer_regs = array_map(
                    'trim',
                    preg_split('/[,\/]+/', $row->get('trailer_reg_numbers') ?? '', -1, PREG_SPLIT_NO_EMPTY)
                );
                foreach ($trailer_regs as $reg) {
                    $trailer = Trailer::where('registration_number', 'LIKE', '%' . trim($reg) . '%')->first();
                    if ($trailer) {
                        $trailer_ids[] = $trailer->id;
                    }
                }

                $freight_calculation = $row->get('rate') ? 'flat_rate' : null;

                if ($cargo?->type === "Solid") {
                    $calculation_measurement = "weight";
                } elseif ($cargo?->type === "Liquid") {
                    $calculation_measurement = "litreage_at_20";
                } else {
                    $calculation_measurement = null;
                }

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
                $trip->rate = $row->get('rate');
                $trip->freight = $row->get('freight');
                $trip->weight = $row->get('weight');
                $trip->litreage = $row->get('litreage_at_ambient');
                $trip->litreage_at_20 = $row->get('litreage_at_20');

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
                // Create Transport Order for this trip row
                // ──────────────────────────────────────────────
                $transport_order = $this->createTransportOrder($trip, $row, [
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
                ]);

                // Link Trip <-> Transport Order
                $trip_transport_order = $this->createTripTransportOrder($trip, $transport_order);

                // Authorize all transport orders linked to this trip
                $this->authorizeTransportOrders($trip);

                // ──────────────────────────────────────────────
                // Trip Expenses
                // ──────────────────────────────────────────────
                $expenseColumn = $row->get('expenses');

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
                                'category' => "Self",
                                'payment_method_id' => PaymentMethod::where('name', 'Cash')->first()?->id,
                            ]
                        );

                        $tripExpense->increment('amount', $amount);
                    }

                    // Create approved Bill + BillExpense + Payment per trip expense
                    foreach ($trip->trip_expenses()->get() as $trip_expense) {
                        $bill = $this->createTripExpenseBill($trip, $trip_expense);
                        if ($bill) {
                            $this->recordImportPayment($bill, $trip_expense);
                        }
                    }
                }
                
                // ──────────────────────────────────────────────
                // Delivery Note (linked to the TTO)
                // ──────────────────────────────────────────────
                $delivery_note = new DeliveryNote();
                $delivery_note->user_id = $user_id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->trip_transport_order_id = $trip_transport_order?->id;
                $delivery_note->loaded_date = $trip->start_date;
                $delivery_note->offloaded_date = $trip->end_date;
                $delivery_note->loaded_litreage = $trip->litreage;
                $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->offloaded_weight = $row->get('offloaded_weight');
                $delivery_note->offloaded_litreage = $row->get('offloaded_litreage_at_ambient');
                $delivery_note->offloaded_litreage_at_20 = $row->get('offloaded_litreage_at_20');
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

                $cost_of_sales = $trip->trip_expenses->sum('amount');
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
        }
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

        $account = $this->payment_account;

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
        $bill->authorization = "approved";
        $bill->authorization_date = now();
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

            // Accrual balance: latest vendor ledger entry minus this payment
            if ($bill->vendor_id) {

                $payments = DB::table('payments')
                    ->select([
                        'vendor_id',
                        'currency_id',
                        DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                        'payments.date',
                        'payments.created_at',
                        DB::raw("'payment' AS source"),
                        'id',
                    ])
                    ->whereNull('deleted_at')
                    ->where('vendor_id', $bill->vendor_id)
                    ->where('currency_id', $bill->currency_id);

                $bills = DB::table('bills')
                    ->select([
                        'vendor_id',
                        'currency_id',
                        DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                        DB::raw('bills.bill_date AS date'),
                        'bills.created_at',
                        DB::raw("'bill' AS source"),
                        'id',
                    ])
                    ->where('authorization', 'approved')
                    ->whereNull('deleted_at')
                    ->where('vendor_id', $bill->vendor_id)
                    ->where('currency_id', $bill->currency_id);

                $latest = DB::query()
                    ->fromSub($payments->unionAll($bills), 'u')
                    ->orderByDesc('date')
                    ->orderByDesc('created_at')
                    ->orderByRaw("CASE WHEN source = 'payment' THEN 1 ELSE 0 END DESC")
                    ->first();

                if ($latest && is_numeric($latest->accrual_balance)) {
                    $payment->accrual_balance = (float) bcsub(
                        (string) $latest->accrual_balance,
                        (string) $amount,
                        2
                    );
                }
            }

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
    public function createTransportOrder($trip, $row, array $lookups)
    {
        $transport_order = new TransportOrder;
        $transport_order->transport_order_number = $this->transportOrderNumber();
        $transport_order->custom_ref = $lookups['trip_ref'];
        $transport_order->bill_of_entry = $row->get('bill_of_entry') ?: null;
        $transport_order->user_id = Auth::id();
        $transport_order->deal_id = null;
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
        $transport_order->multiple_destinations = false;
        $transport_order->quantity = $row->get('quantity') ?: null;
        $transport_order->litreage = $row->get('litreage_at_ambient') ?: null;
        $transport_order->units_of_measure_id = null;
        $transport_order->weight = $row->get('weight') ?: null;
        $transport_order->status = "Pending";
        $transport_order->rate = $row->get('rate') ?: null;
        $transport_order->freight = $row->get('freight') ?: null;
        $transport_order->transporter_rate = null;
        $transport_order->transporter_freight = null;
        $transport_order->exchange_rate = null;
        $transport_order->exchange_customer_freight = null;
        $transport_order->distance = $row->get('distance') ?: null;
        $transport_order->save();

        $this->addDestinations($transport_order, $row, $lookups);
        $this->addOrigins($transport_order, $row, $lookups);

        return $transport_order;
    }

    public function addDestinations($transport_order, $row, array $lookups)
    {
        TripDestination::where('transport_order_id', $transport_order->id)->delete();

        TripDestination::updateOrCreate(
            [
                'transport_order_id'  => $transport_order->id,
                'destination_id'      => $transport_order->to,
                'offloading_point_id' => $lookups['offloading_point']?->id,
            ],
            [
                'user_id'             => Auth::id(),
                'weight'              => $row->get('offloaded_weight') ?: $row->get('weight'),
                'quantity'            => $row->get('quantity') ?: null,
                'units_of_measure_id' => null,
                'litreage'            => $row->get('offloaded_litreage_at_ambient') ?: $row->get('litreage_at_ambient'),
                'rate'                => $row->get('rate') ?: null,
                'freight'             => $row->get('freight') ?: null,
            ]
        );
    }

    public function addOrigins($transport_order, $row, array $lookups)
    {
        TripOrigin::where('transport_order_id', $transport_order->id)->delete();

        TripOrigin::updateOrCreate(
            [
                'transport_order_id' => $transport_order->id,
                'destination_id'     => $transport_order->from,
                'loading_point_id'   => $lookups['loading_point']?->id,
            ],
            [
                'user_id'             => Auth::id(),
                'weight'              => $row->get('weight') ?: null,
                'quantity'            => $row->get('quantity') ?: null,
                'units_of_measure_id' => null,
                'litreage'            => $row->get('litreage_at_ambient') ?: null,
                'rate'                => $row->get('rate') ?: null,
                'freight'             => $row->get('freight') ?: null,
            ]
        );
    }

    // ──────────────────────────────────────────────
    // Trip <-> Transport Order link (import version)
    // ──────────────────────────────────────────────
    public function createTripTransportOrder($trip, $transport_order)
    {
        $trip_transport_order = TripTransportOrder::firstOrNew([
            'trip_id'            => $trip->id,
            'transport_order_id' => $transport_order->id,
        ]);

        $trip_transport_order->tto_number          = $this->ttoNumber();
        $trip_transport_order->allocated_quantity  = $transport_order->quantity;
        $trip_transport_order->allocated_weight    = $transport_order->weight;
        $trip_transport_order->allocated_litreage  = $transport_order->litreage;
        $trip_transport_order->allocated_freight   = $transport_order->freight;
        $trip_transport_order->deal_id             = $transport_order->deal_id;
        $trip_transport_order->allocated_rate      = $transport_order->rate;
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
