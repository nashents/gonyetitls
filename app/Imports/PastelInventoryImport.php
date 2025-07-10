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

class PastelInventoryImport implements ToCollection, SkipsEmptyRows, WithLimit,
    WithHeadingRow, SkipsOnError, WithValidation, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors;

    protected $company;
    protected $default_currency;
    protected $initialInventoryId;
    protected $initialProductId;
    protected $department;

    public function __construct($department)
    {
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->default_currency = $this->company->currency;
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
           
            $category = Category::firstOrCreate(['name' => trim($row->get('type'))], ['status' => 1]);
            $subCategory = CategoryValue::firstOrCreate(['name' => trim($row->get('category'))], ['status' => 1]);
            $product = Product::firstOrNew(['name' => $row->get('description')]);

            if (!$product->exists) {
                $product->fill([
                    'user_id' => Auth::id(),
                    'unit_of_measure' => $row->get('uom'),
                    'category_id' => $category->id,
                    'category_value_id' => $subCategory->id,
                    'status' => 1,
                    'buy' => 1,
                    'product_number' => $this->generateNumber('P', ++$this->initialProductId),
                    'department' => $this->department,
                    'identification_number' => $row->get('code'),
                ])->save();
            }

            $quantity = (int) $row->get('qty');
            $unitPrice = $row->get('unit_cost');
         
            for ($i = 0; $i < $quantity; $i++) {

                if ($this->department == "inventory") {

                    $inventory = new Inventory;
                    $inventory->fill([
                        'user_id' => Auth::id(),
                        'inventory_number' => $this->generateNumber('I', ++$this->initialInventoryId),
                        'product_id' => $product->id,
                        'department' => $this->department,
                        'amount' => $unitPrice,
                        'subtotal' => $unitPrice,
                        'qty' => 1,
                        'subtotal_incl' => $unitPrice,
                        'total' => $unitPrice,
                        'currency_id' => $this->default_currency->id,
                        'weight' => 1,
                        'balance' => 1,
                        'status' => 1,
                    ])->save();
                }
              
            }
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
