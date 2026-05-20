<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BillsImport implements ToCollection, WithHeadingRow, WithValidation
{
    use SkipsErrors;
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
        foreach ($rows as $index => $row) {


            DB::transaction(function () use ($row) {

            

                  $vendor_name  = $row->get('vendor_name');
                $currency     = $row->get('currency');
                $account_name = $row->get('account_name');
                $bill_for     = $row->get('bill_for');
                $number       = $row->get('number');
                $items        = $row->get('items');

                // Skip entirely empty rows (happens when Excel has trailing blank rows)
                if (!$vendor_name && !$currency && !$items) return;

                throw_if(!$vendor_name,  \Exception::class, "Missing vendor_name on a row.");
                throw_if(!$currency,     \Exception::class, "Missing currency on a row.");
                throw_if(!$bill_for,     \Exception::class, "Missing bill_for on a row.");
                throw_if(!$number,       \Exception::class, "Missing number on a row.");
                throw_if(!$items,        \Exception::class, "Missing items on a row.");

                $vendorId                  = $this->resolveVendor($vendor_name);
                $currencyId                = $this->resolveCurrency($currency);
                $accountId                 = $this->resolveAccount($account_name ?? '');
                [$entityColumn, $entityId] = $this->resolveEntity($bill_for, $number);

                $bill                     = new Bill();
                $bill->user_id            = Auth::id();
                $bill->vendor_id          = $vendorId;
                $bill->bill_for           = $row->get('bill_for');
                $bill->category           = $row->get('bill_for');
                $bill->currency_id        = $currencyId;
                $bill->bill_number        = $this->billNumber();
                $bill->bill_date          = $row->get('bill_date');
                $bill->notes              = $row->get('notes');
                $bill->transporter_id     = null;
                $bill->horse_id           = null;
                $bill->driver_id          = null;
                $bill->vehicle_id         = null;
                $bill->trailer_id         = null;
                $bill->asset_id           = null;
                $bill->{$entityColumn}    = $entityId;
                $bill->authorization      = 'approved';
                $bill->authorization_date = now();
                $bill->authorized_by_id   = Auth::id();
                $bill->save();

                $subtotal = 0;
                $total    = 0;
                $account  = Account::find($accountId);

                foreach ($this->parseItems($row->get('items')) as $item) {
                    $productId = $this->resolveProduct($item['name']);

                    $line_subtotal = $item['qty'] * $item['unit_price'];

                    $expense                  = new BillExpense();
                    $expense->bill_id         = $bill->id;
                    $expense->currency_id     = $currencyId;
                    $expense->product_id      = $productId;
                    $expense->description     = $item['name'];
                    $expense->qty             = $item['qty'];
                    $expense->amount          = $item['unit_price'];
                    $expense->subtotal        = $line_subtotal;
                    $expense->subtotal_incl   = $line_subtotal;
                    $expense->account_id      = $accountId;
                    $expense->account_type_id = $account?->account_type?->id;
                    $expense->save();

                    $subtotal += $line_subtotal;
                    $total    += $line_subtotal;
                }

                $bill->subtotal   = $subtotal;
                $bill->tax_amount = 0;
                $bill->total      = $total;
                $bill->balance    = 0;
                $bill->status    = "Paid";
                $bill->save();
            });
        }
    }

    // ── Bill number generator ─────────────────────────────────────────────

    protected function billNumber(): string
    {
        $user = Auth::user();

        if (isset($user->company)) {
            $str = $user->company->name;
        } elseif (isset($user->employee->company)) {
            $str = $user->employee->company->name;
        } else {
            $str = 'XX';
        }

        $words    = explode(' ', $str);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];

        $last   = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return $initials . 'B' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // ── Items parser ──────────────────────────────────────────────────────
    // Format: "Air Filter*2:20.00, Engine Oil*4:12.50"

    protected function parseItems(string $items_string): array
    {
        $parsed = [];

        foreach (explode(',', $items_string) as $segment) {
            $segment = trim($segment);
            if (empty($segment)) continue;

            $colon_pos  = strrpos($segment, ':');
            $unit_price = (float) trim(substr($segment, $colon_pos + 1));
            $left       = trim(substr($segment, 0, $colon_pos));

            $asterisk_pos = strrpos($left, '*');
            $qty          = (float) trim(substr($left, $asterisk_pos + 1));
            $name         = trim(substr($left, 0, $asterisk_pos));

            $parsed[] = compact('name', 'qty', 'unit_price');
        }

        return $parsed;
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
            $id = Account::whereRaw('LOWER(name) = ?', [$key])->value('id');

            if (!$id) {
                $id = Account::whereRaw('LOWER(name) = ?', ['uncategorized expense'])->value('id');
                throw_if(!$id, \Exception::class, "Account '{$name}' not found and 'Uncategorized Expense' fallback does not exist.");
            }

            $this->accountCache[$key] = $id;
        }
        return $this->accountCache[$key];
    }

    protected function resolveEntity(string $bill_for, string $number): array
    {
        $cache_key = strtolower("{$bill_for}:{$number}");

        if (!isset($this->entityCache[$cache_key])) {
            [$column, $id] = match ($bill_for) {
                'Horse'       => ['horse_id',       Horse::whereRaw('LOWER(registration_number) = ?',      [strtolower($number)])->value('id')],
                'Trailer'     => ['trailer_id',     Trailer::whereRaw('LOWER(registration_number) = ?',    [strtolower($number)])->value('id')],
                'Vehicle'     => ['vehicle_id',     Vehicle::whereRaw('LOWER(registration_number) = ?',    [strtolower($number)])->value('id')],
                'Driver'      => ['driver_id',      Driver::whereRaw('LOWER(driver_number) = ?',           [strtolower($number)])->value('id')],
                'Transporter' => ['transporter_id', Transporter::whereRaw('LOWER(transporter_number) = ?', [strtolower($number)])->value('id')],
                'Asset'       => ['asset_id',       Asset::whereRaw('LOWER(asset_number) = ?',             [strtolower($number)])->value('id')],
                default       => throw new \Exception("Unknown bill_for: {$bill_for}"),
            };

            throw_if(!$id, \Exception::class, "{$bill_for} not found: {$number}");
            $this->entityCache[$cache_key] = [$column, $id];
        }

        return $this->entityCache[$cache_key];
    }

    // ── Validation ────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            // '*.vendor_name'  => 'required|string',
            // '*.bill_for'     => 'required|in:Transporter,Horse,Asset,Driver,Trailer,Vehicle',
            // '*.number'       => 'required|string',
            // '*.bill_date'    => 'required|date',
            // '*.currency'     => 'required|string',
            // '*.items'        => 'required|string',
            // '*.account_name' => 'required|string',
        ];
    }
}
