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
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
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
                $bill_date       = $this->parseExcelDate($row->get('bill_date'));
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
                [$entityColumn, $entityId] = ($bill_for && $value) ? $this->resolveEntity($bill_for, (string) $value) : [null, null];
                // If the entity couldn't be matched, drop bill_for/value entirely
                // rather than leaving a dangling category with no linked record.
                $resolvedBillFor = $entityColumn ? $bill_for : null;

                $line_subtotal = (float) $qty * (float) $unit_price;
                $line_total    = $total ? (float) $total : $line_subtotal;

                $bill                     = new Bill();
                $bill->user_id            = Auth::id();
                $bill->vendor_id          = $vendorId;
                $bill->bill_for           = $resolvedBillFor;
                $bill->category           = $resolvedBillFor;
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
                $bill->balance            = $line_total;
                $bill->status             = 'Unpaid';
                // Stay "pending" until the expense line below exists, so the
                // BillObserver doesn't post a journal entry with no debit line.
                $bill->authorization      = 'pending';
                $bill->to_be_paid         = true;
                $bill->authorized_by_id   = Auth::id();
                $bill->save();

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

                // Approve now that the expense line exists — this is what
                // triggers BillObserver -> BillJournalService::post().
                $bill->authorization      = 'approved';
                $bill->authorization_date = now();
                $bill->save();
            });
        }
    }

    private function parseExcelDate($value)
    {
        if (!isset($value)) {
            return null;
        }

        // Numeric Excel serial
        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                );
            } catch (\Exception $e) {
                return null;
            }
        }

        // String date, normalize separators
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

            // If the named entity can't be matched, drop the link rather than
            // failing the whole row — the bill still gets imported.
            $this->entityCache[$cache_key] = $id ? [$column, $id] : [null, null];
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