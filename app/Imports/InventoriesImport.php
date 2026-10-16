<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;

use Carbon\Carbon;
use App\Models\Bin;
use App\Models\Rack;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Inventory;
use App\Models\ExchangeRate;
use App\Models\CategoryValue;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;

/**
 * Opening stock for a new system user - stock already on hand whose vendors
 * aren't known any more. Rows are tagged purchase_type 'Opening Balance' (no
 * GRV, vendor or bill); the caller runs it inside a transaction and posts the
 * value via OpeningStockJournalService, so a bad row (unknown currency,
 * missing exchange rate) throws and nothing is imported.
 */
class InventoriesImport implements ToCollection, SkipsEmptyRows, WithLimit,
    WithHeadingRow, WithValidation, WithChunkReading
{
    use SkipsTemplateExampleRows;

    use Importable;

    public const PURCHASE_TYPE = 'Opening Balance';

    protected $company;
    protected $initialInventoryId;
    protected $initialProductId;
    protected $purchaseDate;
    protected $rowNumber = 1; // heading row

    public function __construct(?string $purchaseDate = null)
    {
        $this->company = Auth::user()->employee->company;
        $this->initialInventoryId = Inventory::max('id') ?? 0;
        $this->initialProductId = Product::max('id') ?? 0;
        $this->purchaseDate = $purchaseDate ?: now()->toDateString();
    }

    public function limit(): int
    {
        return 2500;
    }

    private function generateNumber($prefix, $id)
    {
        $initials = collect(explode(' ', $this->company->name))->map(fn($word) => $word[0])->implode('');
        return $initials . $prefix . str_pad($id + 1, 5, '0', STR_PAD_LEFT);
    }

    private function parseExcelDate($value)
    {
        if (!isset($value)) return null;
        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                return null;
            }
        }
        if (is_string($value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /** Existing record by case-insensitive trimmed name, else a new one; null when blank. */
    private function findOrCreate(string $model, string $column, $value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $existing = $model::whereRaw("LOWER(TRIM({$column})) = ?", [mb_strtolower($value)])->first();
        if ($existing) {
            return $existing;
        }

        $record = new $model;
        $attributes = [$column => $value];
        if (Schema::hasColumn($record->getTable(), 'status')) {
            $attributes['status'] = 1;
        }
        $record->forceFill($attributes)->save();

        return $record;
    }

    /** Must already exist (by name, symbol or full name) - blank means the company currency. */
    private function currency($value, string $label): Currency
    {
        $typed = trim((string) $value);
        $value = mb_strtolower($typed);

        if ($value === '') {
            $currency = Currency::find($this->company->currency_id);
        } else {
            $currency = Currency::where(function ($q) use ($value) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [$value])
                    ->orWhereRaw('LOWER(TRIM(symbol)) = ?', [$value])
                    ->orWhereRaw('LOWER(TRIM(fullname)) = ?', [$value]);
            })->first();
        }

        if (!$currency) {
            $this->fail($label, $value === ''
                ? 'no currency given and the company has no default currency.'
                : "currency \"{$typed}\" doesn't exist - add it under Currencies or use an existing name/symbol.");
        }

        return $currency;
    }

    /** Multiplier into company currency: the sheet's exchange_rate, else the configured rate. */
    private function exchangeRate(Currency $currency, $sheetRate, string $label): float
    {
        if ((int) $currency->id === (int) $this->company->currency_id) {
            return 1.0;
        }

        if (is_numeric($sheetRate) && (float) $sheetRate > 0) {
            return (float) $sheetRate;
        }

        $rate = ExchangeRate::where('currency_id', $currency->id)
            ->where('status', 1)
            ->where('expiry', '>', Carbon::today())
            ->latest('id')
            ->value('exchange_rate');

        if (!is_numeric($rate) || (float) $rate <= 0) {
            $this->fail($label, "no active {$currency->name} exchange rate - set one under Exchange Rates or fill the exchange_rate column.");
        }

        return (float) $rate;
    }

    private function fail(string $label, string $message): void
    {
        throw ValidationException::withMessages(['importFile' => "{$label}: {$message} Nothing was imported."]);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rowNumber++;
            if ($row->filter()->isEmpty()) continue;

            $productName = trim((string) $row->get('product_name'));
            $label = "Row {$this->rowNumber}" . ($productName !== '' ? " ({$productName})" : '');

            if ($productName === '') {
                $this->fail($label, 'product_name is empty.');
            }

            $quantity = $row->get('quantity');
            if (!is_numeric($quantity) || (float) $quantity <= 0) {
                $this->fail($label, 'quantity must be a number greater than 0.');
            }
            $quantity = (float) $quantity;

            $unitPrice = $row->get('unit_price');
            $unitPrice = is_numeric($unitPrice) ? (float) $unitPrice : 0;

            $currency = $this->currency($row->get('currency'), $label);
            $rate = $this->exchangeRate($currency, $row->get('exchange_rate'), $label);

            $store = $this->findOrCreate(Store::class, 'name', $row->get('store_name'));
            $category = $this->findOrCreate(Category::class, 'name', $row->get('category'));
            $subCategory = $this->findOrCreate(CategoryValue::class, 'name', $row->get('sub_category'));
            $brand = $this->findOrCreate(Brand::class, 'name', $row->get('brand_name'));
            $rack = $this->findOrCreate(Rack::class, 'rack_number', $row->get('rack_number'));
            $bin = $this->findOrCreate(Bin::class, 'bin_number', $row->get('bin_number'));
            $uom = $this->findOrCreate(UnitsOfMeasure::class, 'name', ucfirst(strtolower(trim((string) $row->get('unit_of_measure')))));

            $product = Product::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($productName)])->first();

            if (!$product) {
                $product = new Product;
                $product->forceFill([
                    'name' => $productName,
                    'user_id' => Auth::id(),
                    'unit_of_measure' => $uom?->name,
                    'category_id' => $category?->id,
                    'category_value_id' => $subCategory?->id,
                    'brand_id' => $brand?->id,
                    'price' => $unitPrice,
                    'status' => 1,
                    'buy' => 1,
                    'product_number' => $this->generateNumber('IP', ++$this->initialProductId),
                    'department' => 'inventory',
                    'identification_number' => $row->get('part_number'),
                ])->save();
            }

            $subtotal = $unitPrice * $quantity;
            $balance = $row->get('balance');

            $inventory = new Inventory;
            $inventory->forceFill([
                'user_id' => Auth::id(),
                'inventory_number' => $this->generateNumber('I', ++$this->initialInventoryId),
                'product_id' => $product->id,
                'amount' => $unitPrice,
                'subtotal' => $subtotal,
                'qty' => $quantity,
                'subtotal_incl' => $subtotal,
                'total' => $subtotal,
                'currency_id' => $currency->id,
                // Dispatches price foreign-currency rows off exchange_amount.
                'exchange_rate' => $rate,
                'exchange_amount' => $rate * $subtotal,
                'rack_id' => $rack?->id,
                'bin_id' => $bin?->id,
                'store_id' => $store?->id,
                'purchase_date' => optional($this->parseExcelDate($row->get('purchase_date')))->toDateString() ?? $this->purchaseDate,
                'purchase_type' => self::PURCHASE_TYPE,
                'condition' => 'New',
                'weight' => $row->get('item_contents') ?: 1,
                'balance' => is_numeric($balance) ? (float) $balance : $quantity,
                'status' => 1,
                'disposed' => 0,
            ])->save();
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
