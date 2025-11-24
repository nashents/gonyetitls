<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Bin;
use App\Models\Rack;
use App\Models\Brand;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Inventory;
use App\Models\CategoryValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class InventoriesImport implements ToCollection, SkipsEmptyRows, WithLimit,
    WithHeadingRow, SkipsOnError, WithValidation, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors;

    protected $company;
    protected $initialInventoryId;
    protected $initialProductId;

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
        $this->initialInventoryId = Inventory::max('id') ?? 0;
        $this->initialProductId = Product::max('id') ?? 0;
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

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row->filter()->isEmpty()) continue;

            $store = Store::firstOrCreate(['name' => trim($row->get('store_name'))], ['status' => 1]);
            $category = Category::firstOrCreate(['name' => trim($row->get('category'))], ['status' => 1]);
            $subCategory = CategoryValue::firstOrCreate(['name' => trim($row->get('sub_category'))], ['status' => 1]);
            $brand = Brand::firstOrCreate(['name' => trim($row->get('brand_name'))], ['status' => 1]);
            $rack = Rack::firstOrCreate(['rack_number' => trim($row->get('rack_number'))], ['status' => 1]);
            $bin = Bin::firstOrCreate(['bin_number' => trim($row->get('bin_number'))], ['status' => 1]);
            $currency = Currency::firstOrCreate(['name' => trim($row->get('currency'))], ['status' => 1]);

            $product = Product::firstOrNew(['name' => $row->get('product_name')]);

            if (!$product->exists) {
                $product->fill([
                    'user_id' => Auth::id(),
                    'unit_of_measure' => $row->get('unit_of_measure'),
                    'category_id' => $category->id,
                    'category_value_id' => $subCategory->id,
                    'brand_id' => $brand->id,
                    'status' => 1,
                    'buy' => 1,
                    'product_number' => $this->generateNumber('IP', ++$this->initialProductId),
                    'department' => 'inventory',
                    'identification_number' => $row->get('part_number'),
                ])->save();
            }

            $quantity = (int) $row->get('quantity');
            $unitPrice = $row->get('unit_price');
            $subtotal = 0;
            if (is_numeric($unitPrice) && is_numeric($quantity)) {
                $subtotal = $unitPrice * $quantity;
            }
          


           
                $inventory = new Inventory;
                $inventory->fill([
                    'user_id' => Auth::id(),
                    'inventory_number' => $this->generateNumber('I', ++$this->initialInventoryId),
                    'product_id' => $product->id,
                    'amount' => $unitPrice,
                    'subtotal' => $subtotal,
                    'qty' => $quantity,
                    'subtotal_incl' => $subtotal,
                    'total' => $subtotal,
                    'currency_id' => $currency->id,
                    'rack_id' => $rack->id,
                    'bin_id' => $bin->id,
                    'store_id' => $store->id,
                    'purchase_date' => $this->parseExcelDate($row->get('purchase_date')),
                    'weight' => $row->get('item_contents') ?: 1,
                    'balance' => $row->get('balance') ?: 1,
                    'status' => 1,
                ])->save();
           
        }
    }

    public function rules(): array
    {
        return [];
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
