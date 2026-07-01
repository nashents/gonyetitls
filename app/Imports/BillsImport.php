<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\AccountTypeGroup;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Horse;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithValidation;

class BillsImport implements ToCollection ,SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use SkipsErrors, Importable;
    use SkipsFailures;
    
    

    protected $company;

    protected array $currencyCache = [];
    protected array $accountCache  = [];
    protected array $entityCache   = [];
    protected array $vendorCache   = [];
    protected array $productCache  = [];

    public function __construct($company)
    {
        $this->company = $company;
    }

    public function collection(Collection $rows)
    {
        $errors = [];
        

        foreach ($rows as $row) {

          
            DB::transaction(function () use ($row) {

               

                $vendor_name     = $row->get('vendor_name');
                $bill_for        = $row->get('bill_for');
                $value           = $row->get('value');
                $bill_date       = $row->get('bill_date');
                $item_name       = $row->get('items');
                $qty             = $row->get('qty') ?: 1;
                $currency        = $row->get('currency');
                $unit_price      = $row->get('unit_price');
                $total           = $row->get('total');
                $notes           = $row->get('notes');
                $expense_account = $row->get('expense_account');

                $vendorId                  = $vendor_name ? $this->resolveVendor($vendor_name) : null;
                $currencyId                = $this->resolveCurrency($currency);
                $accountId                 = $this->resolveAccount($expense_account ?? '');
                [$entityColumn, $entityId] = $bill_for ? $this->resolveEntity($bill_for, $value) : [null, null];

                $line_subtotal = (float) $qty * (float) $unit_price;
                $line_total    = $total ? (float) $total : $line_subtotal;

                $bill                     = new Bill();
                $bill->user_id            = Auth::id();
                $bill->vendor_id          = $vendorId;
                $bill->bill_for           = $bill_for;
                $bill->category           = $bill_for;
                $bill->currency_id        = $currencyId;
                $bill->bill_number        = $this->billNumber();
                $bill->bill_date          = $bill_date;
                $bill->notes              = $notes;
                $bill->transporter_id     = null;
                $bill->horse_id           = null;
                $bill->driver_id          = null;
                $bill->employee_id        = null;
                $bill->vehicle_id         = null;
                $bill->trailer_id         = null;
                $bill->asset_id           = null;
                if ($entityColumn) {
                    $bill->{$entityColumn}    = $entityId;
                }
                $bill->subtotal           = $line_subtotal;
                $bill->tax_amount         = 0;
                $bill->total              = $line_total;
                $bill->balance            = 0;
                $bill->status             = 'Paid';
                $bill->authorization      = 'approved';
                $bill->authorization_date = now();
                $bill->authorized_by_id   = Auth::id();
                $bill->save();

               

                $this->recordPayment($bill, $currencyId, $accountId, $line_total);

                $account = Account::find($accountId);

                $expense                  = new BillExpense();
                $expense->bill_id         = $bill->id;
                $expense->currency_id     = $currencyId;
                $expense->product_id      = $this->resolveProduct($item_name);
                $expense->description     = $item_name;
                $expense->qty             = (float) $qty;
                $expense->amount          = (float) $unit_price;
                $expense->subtotal        = $line_subtotal;
                $expense->subtotal_incl   = $line_subtotal;
                $expense->account_id      = $accountId;
                $expense->account_type_id = $account?->account_type?->id;
                $expense->save();

               
            });
        }
    }

    protected function recordPayment(Bill $bill, int $currencyId, int $accountId, float $amount): void
    {
        $account = Account::find($accountId);

        $payment                      = new Payment();
        $payment->vendor_id           = $bill->vendor_id;
        $payment->bill_id             = $bill->id;
        $payment->movement            = 'Dbt';
        $payment->description         = $bill->notes;
        $payment->user_id             = Auth::id();
        $payment->currency_id         = $currencyId;
        $payment->payment_number      = $this->paymentNumber();
        $payment->category            = 'Bill';
        $payment->account_id          = $accountId;
        $payment->amount              = $amount;
        $payment->balance             = 0;
        $payment->date                = $bill->bill_date;
        $payment->save();

        if ($account) {
            $account->balance = ($account->balance ?? 0) - $amount;
            $account->save();
        }
    }

    protected function paymentNumber(): string
    {
        $user = Auth::user();

        $str = "";
        $str = $user->employee->company?->name;

        $words    = explode(' ', $str);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];

        $last   = Payment::latest()->orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return $initials . 'P' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // ── Bill number generator ─────────────────────────────────────────────

    protected function billNumber(): string
    {
        $user = Auth::user();
        $str = "";
        $str = $user->employee->company?->name;
            
        $words    = explode(' ', $str);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];

        $last   = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return $initials . 'B' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // ── Lookup helpers ────────────────────────────────────────────────────

    protected function resolveVendor(string $name): int
    {
        $key = strtolower(trim($name));
        if (!isset($this->vendorCache[$key])) {
            $this->vendorCache[$key] = Vendor::firstOrCreate(['name' => $name])->id;
        }
        return $this->vendorCache[$key];
    }

    protected function resolveProduct(string $name): int
    {
        $key = strtolower(trim($name));
        if (!isset($this->productCache[$key])) {
            $this->productCache[$key] = Product::firstOrCreate(['name' => $name])->id;
        }
        return $this->productCache[$key];
    }

    protected function resolveCurrency(string $code): int
    {
        $key = strtolower(trim($code));
        if (!isset($this->currencyCache[$key])) {
            $id = Currency::whereRaw('LOWER(name) = ?', [$key])->value('id');
            throw_if(!$id, \Exception::class, "Currency not found: {$code}");
            $this->currencyCache[$key] = $id;
        }
        return $this->currencyCache[$key];
    }

    protected function resolveAccount(string $name): int
    {
        $key = strtolower(trim($name));
        if (!isset($this->accountCache[$key])) {
            $expenseGroup = AccountTypeGroup::where('name', 'Expenses')->first();
            $operatingExpense = AccountType::where('name', 'Operating Expense')->first();

            $account = Account::firstOrCreate(
                ['name' => $name],
                [
                    'account_type_group_id' => $expenseGroup?->id,
                    'account_type_id'       => $operatingExpense?->id,
                ]
            );

            $this->accountCache[$key] = $account->id;
        }
        return $this->accountCache[$key];
    }

    protected function resolveEntity(string $bill_for, string $value): array
    {
        $cache_key = strtolower("{$bill_for}:{$value}");

        if (!isset($this->entityCache[$cache_key])) {
            [$column, $id] = match ($bill_for) {
                'Horse'       => ['horse_id',       Horse::whereRaw('LOWER(registration_number) = ?',   [strtolower($value)])->value('id')],
                'Trailer'     => ['trailer_id',     Trailer::whereRaw('LOWER(registration_number) = ?', [strtolower($value)])->value('id')],
                'Vehicle'     => ['vehicle_id',     Vehicle::whereRaw('LOWER(registration_number) = ?', [strtolower($value)])->value('id')],
                'Transporter' => ['transporter_id', Transporter::whereRaw('LOWER(name) = ?',            [strtolower($value)])->value('id')],
                'Employee' => ['employee_id',Employee::whereRaw("LOWER(TRIM(CONCAT(name, ' ', surname))) = ?",[strtolower(trim($value))])->value('id')],
                'Driver' => ['driver_id', Driver::whereHas('employee', function ($q) use ($value) {
                                $q->whereRaw("LOWER(CONCAT(name, ' ', surname)) = ?", [strtolower($value)]);
                            })->value('id')],
                'Asset'  => ['asset_id',  Asset::whereHas('product', function ($q) use ($value) {
                                                $q->whereRaw('LOWER(name) = ?', [strtolower($value)]);
                                            })->value('id')],
                default       => throw new \Exception("Unknown bill_for: {$bill_for}"),
            };

            throw_if(!$id, \Exception::class, "{$bill_for} not found: {$value}");
            $this->entityCache[$cache_key] = [$column, $id];
        }

        return $this->entityCache[$cache_key];
    }

    
    public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    // ── Validation ────────────────────────────────────────────────────────

    public function rules(): array{
        return[
            // '*.idnumber' => ['nullable','unique:employees,idnumber,NULL,id,deleted_at,NULL'],
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