<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BillsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts,
{
    use SkipsErrors, SkipsFailures;

    protected $company;

    // Lookup caches to avoid N+1 on large imports
    protected array $vendorCache    = [];
    protected array $currencyCache  = [];
    protected array $accountCache   = [];
    protected array $productCache   = [];
    protected array $entityCache    = [];

    public function __construct($company)
    {
        $this->company = $company;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            
            DB::transaction(function () use ($row) {

                // ── Resolve FKs from names ────────────────────────────────
                $vendorId   = $this->resolveVendor($row['vendor_name']);
                $currencyId = $this->resolveCurrency($row['currency']);
                $accountId  = $this->resolveAccount($row['account_name']);
                [$entityColumn, $entityId] = $this->resolveEntity($row['bill_for'], $row['reg_number']);

                // ── Bill header ───────────────────────────────────────────
                $bill = new Bill();
                $bill->user_id        = Auth::id();
                $bill->vendor_id      = $vendorId;
                $bill->bill_for       = $row['bill_for'];
                $bill->category       = $row['bill_for'];
                $bill->currency_id    = $currencyId;
                $bill->bill_number    = $row['bill_number'];
                $bill->bill_date      = $row['bill_date'];
                $bill->due_date       = $row['due_date'];
                $bill->notes          = $row['notes'] ?? null;

                // Null out all entity FKs then set the relevant one
                $bill->transporter_id = null;
                $bill->horse_id       = null;
                $bill->driver_id      = null;
                $bill->vehicle_id     = null;
                $bill->trailer_id     = null;
                $bill->asset_id       = null;
                $bill->{$entityColumn} = $entityId;

                $bill->save();

                // ── Parse items string ────────────────────────────────────
                // Format: "Air Filter*2:20.00, Engine Oil*4:12.50"
                $lineItems = $this->parseItems($row['items']);

                $subtotal      = 0;
                $total         = 0;
                $account       = Account::find($accountId);

                foreach ($lineItems as $item) {
                    $productId = $this->resolveProduct($item['name']);

                    $lineSubtotal = $item['qty'] * $item['unit_price'];

                    $expense = new BillExpense();
                    $expense->bill_id         = $bill->id;
                    $expense->currency_id     = $currencyId;
                    $expense->product_id      = $productId;
                    $expense->description     = $item['name'];
                    $expense->qty             = $item['qty'];
                    $expense->amount          = $item['unit_price'];
                    $expense->subtotal        = $lineSubtotal;
                    $expense->subtotal_incl   = $lineSubtotal; // No tax on import
                    $expense->account_id      = $accountId;
                    $expense->account_type_id = $account?->account_type?->id;
                    $expense->save();

                    $subtotal += $lineSubtotal;
                    $total    += $lineSubtotal;
                }

                $bill->subtotal   = $subtotal;
                $bill->tax_amount = 0;
                $bill->total      = $total;
                $bill->balance    = $total;
                $bill->save();

                // ── Notifications ─────────────────────────────────────────
                Notification::where('when', 'before')
                    ->where('category', 'Bill Authorization')
                    ->where('status', 1)
                    ->get()
                    ->each(function ($notification) use ($bill) {
                        $email = $notification->email ?? $notification->employee?->email ?? null;
                        if ($email) {
                            Mail::to($email)->send(new \App\Mail\PendingNotificationEmails(
                                $this->company, $notification, $bill
                            ));
                        }
                    });
            });
        }
    }

    // ── Item string parser ────────────────────────────────────────────────
    // "Air Filter*2:20.00, Engine Oil*4:12.50"
    protected function parseItems(string $itemsString): array
    {
        $parsed = [];

        foreach (explode(',', $itemsString) as $segment) {
            $segment = trim($segment);
            if (empty($segment)) continue;

            // Split on last colon to get unit_price
            $colonPos  = strrpos($segment, ':');
            $unitPrice = (float) trim(substr($segment, $colonPos + 1));
            $left      = trim(substr($segment, 0, $colonPos)); // "Air Filter*2"

            // Split on last asterisk to get qty
            $asteriskPos = strrpos($left, '*');
            $qty         = (float) trim(substr($left, $asteriskPos + 1));
            $name        = trim(substr($left, 0, $asteriskPos));

            $parsed[] = [
                'name'       => $name,
                'qty'        => $qty,
                'unit_price' => $unitPrice,
            ];
        }

        return $parsed;
    }

    // ── Lookup helpers ────────────────────────────────────────────────────

    protected function resolveVendor(string $name): int
    {
        $key = strtolower(trim($name));
        if (!isset($this->vendorCache[$key])) {
            $this->vendorCache[$key] = Vendor::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        throw_if(!$this->vendorCache[$key], \Exception::class, "Vendor not found: {$name}");
        return $this->vendorCache[$key];
    }

    protected function resolveCurrency(string $code): int
    {
        $key = strtolower(trim($code));
        if (!isset($this->currencyCache[$key])) {
            // currencies.name stores the currency code (USD, ZWG etc.)
            $this->currencyCache[$key] = Currency::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        throw_if(!$this->currencyCache[$key], \Exception::class, "Currency not found: {$code}");
        return $this->currencyCache[$key];
    }

    protected function resolveAccount(string $name): int
    {
        $key = strtolower(trim($name));
        if (!isset($this->accountCache[$key])) {
            $this->accountCache[$key] = Account::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        throw_if(!$this->accountCache[$key], \Exception::class, "Account not found: {$name}");
        return $this->accountCache[$key];
    }

    protected function resolveProduct(string $name): ?int
    {
        $key = strtolower(trim($name));
        if (!array_key_exists($key, $this->productCache)) {
            $this->productCache[$key] = Product::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        return $this->productCache[$key]; // nullable — product may not exist yet
    }

    protected function resolveEntity(string $billFor, string $regNumber): array
    {
        $cacheKey = strtolower("{$billFor}:{$regNumber}");

        if (!isset($this->entityCache[$cacheKey])) {
            [$column, $id] = match ($billFor) {
                'Horse'       => ['horse_id',       Horse::whereRaw('LOWER(reg_number) = ?',   [strtolower($regNumber)])->value('id')],
                'Trailer'     => ['trailer_id',     Trailer::whereRaw('LOWER(reg_number) = ?', [strtolower($regNumber)])->value('id')],
                'Vehicle'     => ['vehicle_id',     Vehicle::whereRaw('LOWER(reg_number) = ?', [strtolower($regNumber)])->value('id')],
                'Driver'      => ['driver_id',      Driver::whereRaw('LOWER(employee_number) = ?', [strtolower($regNumber)])->value('id')],
                'Transporter' => ['transporter_id', Transporter::whereRaw('LOWER(name) = ?',   [strtolower($regNumber)])->value('id')],
                'Asset'       => ['asset_id',       \App\Models\Asset::whereRaw('LOWER(asset_number) = ?', [strtolower($regNumber)])->value('id')],
                default       => throw new \Exception("Unknown bill_for: {$billFor}"),
            };

            throw_if(!$id, \Exception::class, "{$billFor} not found: {$regNumber}");
            $this->entityCache[$cacheKey] = [$column, $id];
        }

        return $this->entityCache[$cacheKey];
    }

    // ── Validation ────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            '*.bill_number'  => 'required|string',
            '*.vendor_name'  => 'required|string',
            '*.bill_for'     => 'required|in:Transporter,Horse,Asset,Driver,Trailer,Vehicle',
            '*.reg_number'   => 'required|string',
            '*.currency'     => 'required|string',
            '*.bill_date'    => 'required|date',
            '*.due_date'     => 'required|date',
            '*.items'        => 'required|string',
            '*.account_name' => 'required|string',
        ];
    }

    public function batchSize(): int
    {
        return 50;
    }
}
