<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tyre;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithValidation;

class PastelInventoryImport implements ToCollection, SkipsEmptyRows, WithLimit,
    WithHeadingRow, SkipsOnError, WithValidation, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors;

    protected $company;
    protected $default_currency;
    protected int $initialInventoryId;
    protected int $initialProductId;
    protected int $initialTyreId;
    protected string $companyInitials;

    // Lookup caches
    protected array $categoryCache    = [];
    protected array $subCategoryCache = [];
    protected array $productCache     = [];

    // Pre-loaded first-record maps: product_id => record id
    // Used to decide update vs insert without per-row queries
    protected array $existingInventoryMap = []; // product_id => inventory id
    protected array $existingTyreMap      = []; // product_id => tyre id

    public function __construct()
    {
        $this->company          = Auth::user()->employee->company;
        $company                = Company::where('type', '!=', 'Admin')->orderBy('created_at', 'desc')->first();
        $this->default_currency = $company?->currency;

        $this->initialInventoryId = Inventory::max('id') ?? 0;
        $this->initialProductId   = Product::max('id') ?? 0;
        $this->initialTyreId      = Tyre::max('id') ?? 0;

        $this->companyInitials = collect(explode(' ', $this->company->name))
            ->map(fn($word) => $word[0])
            ->implode('');

        // Pre-load categories and subcategories
        $this->categoryCache    = Category::pluck('id', 'name')->toArray();
        $this->subCategoryCache = CategoryValue::pluck('id', 'name')->toArray();

        // Pre-load products keyed by "name|category_id"
        $this->productCache = Product::select('id', 'name', 'category_id')
            ->get()
            ->keyBy(fn($p) => $p->name . '|' . $p->category_id)
            ->map(fn($p) => $p->id)
            ->toArray();

        // Pre-load the FIRST inventory record per product_id
        // If a product has multiple inventory rows, we target only the first one on import
        $this->existingInventoryMap = Inventory::select('id', 'product_id')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id')
            ->map(fn($group) => $group->first()->id)
            ->toArray();

        // Pre-load the FIRST tyre record per product_id
        $this->existingTyreMap = Tyre::select('id', 'product_id')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id')
            ->map(fn($group) => $group->first()->id)
            ->toArray();
    }

    public function limit(): int
    {
        return 15000;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function rules(): array
    {
        return [];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function generateNumber(string $prefix, int $id): string
    {
        return $this->companyInitials . $prefix . str_pad($id, 5, '0', STR_PAD_LEFT);
    }

    private function isTyreCategory(string $categoryName): bool
    {
        $aliases = ['tyre', 'tyres', 'tire', 'tires'];
        $lower   = strtolower(trim($categoryName));

        foreach ($aliases as $alias) {
            if (levenshtein($lower, $alias) <= 1) return true;
        }

        return false;
    }

    private function resolveCategory(string $name): int
    {
        $name = trim($name);

        if (!isset($this->categoryCache[$name])) {
            $cat                        = Category::firstOrCreate(['name' => $name], ['status' => 1]);
            $this->categoryCache[$name] = $cat->id;
        }

        return $this->categoryCache[$name];
    }

    private function resolveSubCategory(string $name): int
    {
        $name = trim($name);

        if (!isset($this->subCategoryCache[$name])) {
            $sub                           = CategoryValue::firstOrCreate(['name' => $name], ['status' => 1]);
            $this->subCategoryCache[$name] = $sub->id;
        }

        return $this->subCategoryCache[$name];
    }

    private function resolveProduct(
        Collection $row,
        int $categoryId,
        int $subCategoryId,
        string $department,
        float $unitPrice
    ): int {
        $name = trim($row->get('description'));
        $key  = $name . '|' . $categoryId;

        if (isset($this->productCache[$key])) {
            // Exists — update fields that may have changed
            Product::where('id', $this->productCache[$key])->update([
                'unit_of_measure'       => $row->get('unit'),
                'category_value_id'     => $subCategoryId,
                'department'            => $department,
                'price'                 => $unitPrice,
                'identification_number' => $row->get('code'),
                'updated_at'            => now(),
            ]);

            return $this->productCache[$key];
        }

        // New product
        $product = new Product();
        $product->fill([
            'user_id'               => Auth::id(),
            'name'                  => $name,
            'unit_of_measure'       => $row->get('unit'),
            'category_id'           => $categoryId,
            'category_value_id'     => $subCategoryId,
            'status'                => 1,
            'department'            => $department,
            'buy'                   => 1,
            'price'                 => $unitPrice,
            'product_number'        => $this->generateNumber('P', ++$this->initialProductId),
            'identification_number' => $row->get('code'),
        ]);
        $product->save();

        $this->productCache[$key] = $product->id;

        return $product->id;
    }

    // -------------------------------------------------------------------------
    // Main collection handler
    // -------------------------------------------------------------------------

    public function collection(Collection $rows)
    {
        $inventoryInserts = [];
        $inventoryUpdates = []; // id => data
        $tyreInserts      = [];
        $tyreUpdates      = []; // id => data

        $currency_id = $this->default_currency?->id ?? 1;
        $userId      = Auth::id();
        $now         = now();

        foreach ($rows as $row) {
            if ($row->filter()->isEmpty()) continue;

            $categoryName  = trim($row->get('type')       ?? '');
            $subCatName    = trim($row->get('category')   ?? '');
            $quantity      = (int)   ($row->get('unposted') ?? 0);
            $unitPrice = (float) ($row->get('cost') ?? 0);
            $department    = $this->isTyreCategory($categoryName) ? 'tyre' : 'inventory';

            $categoryId    = $this->resolveCategory($categoryName);
            $subCategoryId = $this->resolveSubCategory($subCatName);
            $productId     = $this->resolveProduct($row, $categoryId, $subCategoryId, $department, $unitPrice);

            if ($department === 'inventory') {

                if (isset($this->existingInventoryMap[$productId])) {
                    // Target the first existing record for this product
                    $inventoryUpdates[$this->existingInventoryMap[$productId]] = [
                        'amount'        => $unitPrice,
                        'subtotal'      => $unitPrice,
                        'qty'           => $quantity,
                        'subtotal_incl' => $unitPrice,
                        'total'         => $unitPrice,
                        'balance'       => $quantity,
                        'cost'          => $unitPrice,
                        'updated_at'    => $now,
                    ];
                } else {
                    // No existing record — queue for insert
                    $this->existingInventoryMap[$productId] = true; // prevent duplicate inserts in same batch
                    $inventoryInserts[] = [
                        'user_id'          => $userId,
                        'product_id'       => $productId,
                        'currency_id'      => $currency_id,
                        'inventory_number' => $this->generateNumber('I', ++$this->initialInventoryId),
                        'amount'           => $unitPrice,
                        'subtotal'         => $unitPrice,
                        'qty'              => $quantity,
                        'subtotal_incl'    => $unitPrice,
                        'total'            => $unitPrice,
                        'weight'           => 1,
                        'balance'          => $quantity,
                        'status'           => 1,
                        'cost'             => $unitPrice,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

            } elseif ($department === 'tyre') {

                if (isset($this->existingTyreMap[$productId])) {
                    // Target the first existing record for this product
                    $tyreUpdates[$this->existingTyreMap[$productId]] = [
                        'amount'        => $unitPrice,
                        'subtotal'      => $unitPrice,
                        'subtotal_incl' => $unitPrice,
                        'total'         => $unitPrice,
                        'qty'           => $quantity,
                        'balance'       => $quantity,
                        'updated_at'    => $now,
                    ];
                } else {
                    // No existing record — queue for insert
                    $this->existingTyreMap[$productId] = true; // prevent duplicate inserts in same batch
                    $tyreInserts[] = [
                        'product_id'    => $productId,
                        'currency_id'   => $currency_id,
                        'tyre_number'   => $this->generateNumber('T', ++$this->initialTyreId),
                        'amount'        => $unitPrice,
                        'subtotal'      => $unitPrice,
                        'subtotal_incl' => $unitPrice,
                        'total'         => $unitPrice,
                        'qty'           => $quantity,
                        'balance'       => $quantity,
                        'weight'        => 1,
                        'status'        => 1,
                        'disposed'      => 0,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }
        }

        DB::transaction(function () use ($inventoryInserts, $inventoryUpdates, $tyreInserts, $tyreUpdates) {

            // Bulk insert new inventory records
            foreach (array_chunk($inventoryInserts, 500) as $chunk) {
                Inventory::insert($chunk);
            }

            // Bulk update existing inventory records
            // CASE WHEN is more efficient than N individual UPDATE queries
            $this->bulkUpdate('inventories', $inventoryUpdates, [
                'amount', 'subtotal', 'qty', 'subtotal_incl',
                'total', 'balance', 'cost', 'updated_at',
            ]);

            // Bulk insert new tyre records
            foreach (array_chunk($tyreInserts, 500) as $chunk) {
                Tyre::insert($chunk);
            }

            // Bulk update existing tyre records
            $this->bulkUpdate('tyres', $tyreUpdates, [
                'amount', 'subtotal', 'subtotal_incl', 'total',
                'qty', 'balance', 'updated_at',
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Bulk update via CASE WHEN — avoids N individual UPDATE queries
    // $updates = [ id => ['col' => value, ...], ... ]
    // -------------------------------------------------------------------------
    private function bulkUpdate(string $table, array $updates, array $columns): void
    {
        if (empty($updates)) return;

        foreach (array_chunk($updates, 500, true) as $chunk) {
            $ids     = array_keys($chunk);
            $cases   = [];
            $bindings = [];

            foreach ($columns as $column) {
                $when = "CASE id";
                foreach ($chunk as $id => $data) {
                    $when      .= " WHEN ? THEN ?";
                    $bindings[] = $id;
                    $bindings[] = $data[$column] ?? null;
                }
                $when    .= " END";
                $cases[]  = "`{$column}` = {$when}";
            }

            $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "UPDATE `{$table}` SET " . implode(', ', $cases) . " WHERE id IN ({$idPlaceholders})";

            DB::statement($sql, array_merge($bindings, $ids));
        }
    }
}