<?php

namespace App\Imports;


use App\Models\Asset;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Employee;
use App\Models\Horse;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tax;
use App\Models\Trailer;
use App\Models\Tyre;
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

/**
 * DispatchesImport
 *
 * Expected columns (all lowercase, snake_case heading row):
 *   date              - required, Y-m-d
 *   dispatch_for      - required, "inventory" or "expenses"
 *   department        - required, "inventory", "tyre", or "asset"
 *   employee_name     - optional, matched via concat(name,' ',surname)
 *   horse             - optional, registration_number or fleet_number
 *   trailer           - optional, registration_number or fleet_number
 *   vehicle           - optional, registration_number or fleet_number
 *   store_name        - optional
 *   branch_name       - optional
 *   department_name   - optional (asset department)
 *   currency          - optional, defaults to company currency
 *   description       - optional
 *   vendor_name       - required when dispatch_for=expenses
 *   items             - required
 *
 * Items compact format:
 *   inventory/tyre/asset:  "ProductName*qty, ProductName*qty"
 *   expenses:              "ProductName*qty:unit_price:TaxName, ..."  (TaxName optional)
 *
 * Examples:
 *   inventory: "Engine Oil*5, Air Filter*2"
 *   expenses:  "Fuel*10:2.50:VAT 15%, Labour*1:150.00"
 */
class DispatchesImport implements ToCollection, WithHeadingRow, WithValidation
{
    use SkipsErrors;
    use SkipsFailures;

    protected $company;
    protected array $currencyCache   = [];
    protected array $storeCache      = [];
    protected array $branchCache     = [];
    protected array $deptCache       = [];
    protected array $employeeCache   = [];
    protected array $vendorCache     = [];
    protected array $productCache    = [];
    protected array $taxCache        = [];
    protected array $paymentCache    = [];
    protected array $horseCache      = [];
    protected array $trailerCache    = [];
    protected array $vehicleCache    = [];

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
    }

    // ── Main collection handler ───────────────────────────────────────────

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {

            // Skip blank rows
            if (blank($row->get('date')) && blank($row->get('items'))) {
                continue;
            }

            DB::transaction(function () use ($row) {

                $dispatchFor = strtolower(trim($row->get('dispatch_for') ?? 'inventory'));
                $department  = strtolower(trim($row->get('department')   ?? 'inventory'));

                // ── Resolve optional FK lookups ───────────────────────────

                $currencyId   = $this->resolveCurrency($row->get('currency'));
                $storeId      = $this->resolveStore($row->get('store_name'));
                $branchId     = $this->resolveBranch($row->get('branch_name'));
                $deptId       = $this->resolveDepartment($row->get('department_name'));
                $employeeId   = $this->resolveEmployee($row->get('employee_name'));
                $vendorId     = $this->resolveVendor($row->get('vendor_name'));
                $horseId      = $this->resolveHorse($row->get('horse'));
                $trailerId    = $this->resolveTrailer($row->get('trailer'));
                $vehicleId    = $this->resolveVehicle($row->get('vehicle'));

                // ── Build dispatch number ─────────────────────────────────

                $dispatchNumber = $this->dispatchNumber();

                // ── Create Dispatch ───────────────────────────────────────

                $dispatch = new Dispatch;
                $dispatch->user_id        = Auth::id();
                $dispatch->dispatch_number = $dispatchNumber;
                $dispatch->date           = $row->get('date');
                $dispatch->dispatch_for   = $dispatchFor;
                $dispatch->department     = $department;
                $dispatch->description    = $row->get('description');
                $dispatch->currency_id    = $currencyId;
                $dispatch->store_id       = $storeId;
                $dispatch->branch_id      = $branchId;
                $dispatch->department_id  = $deptId;
                $dispatch->employee_id    = $employeeId;
                $dispatch->vendor_id      = $vendorId;
                $dispatch->horse_id       = $horseId;
                $dispatch->trailer_id     = $trailerId;
                $dispatch->vehicle_id     = $vehicleId;
                $dispatch->expand         = false;
                $dispatch->save();

                // ── Parse & persist items ─────────────────────────────────

                $total = 0.0;

                $rawItems = $row->get('items') ?? '';
                $segments = array_filter(array_map('trim', explode(',', $rawItems)));

                foreach ($segments as $segment) {

                    if ($dispatchFor === 'inventory') {
                        $total += $this->createInventoryItem($dispatch, $segment, $department);
                    } elseif ($dispatchFor === 'expenses') {
                        $total += $this->createExpenseItem($dispatch, $segment);
                    }
                }

                $dispatch->total = $total;
                $dispatch->save();
            });
        }
    }

    // ── Item creators ─────────────────────────────────────────────────────

    /**
     * inventory/tyre/asset item: "ProductName*qty"
     */
    protected function createInventoryItem(Dispatch $dispatch, string $segment, string $department): float
    {
        // Parse: ProductName*qty
        if (!str_contains($segment, '*')) {
            return 0.0;
        }

        [$productName, $qty] = explode('*', $segment, 2);
        $productName = trim($productName);
        $qty         = (float) trim($qty);

        if ($qty <= 0 || blank($productName)) {
            return 0.0;
        }

        $product = $this->resolveProduct($productName, $department);
        if (!$product) {
            return 0.0;
        }

        // FIFO: grab source rows oldest-first
        $items = $this->getSourceRows($product->id, $department);

        if ($items->isEmpty()) {
            return 0.0;
        }

        $remaining   = $qty;
        $lineTotal   = 0.0;

        foreach ($items as $item) {

            if ($remaining <= 0) {
                break;
            }

            $rowQty = (float) $item->balance;
            if ($rowQty <= 0) {
                continue;
            }

            $rowCost  = $item->currency_id != $this->company->currency_id
                ? (float) $item->exchange_amount
                : (float) $item->total;

            $unitCost    = $rowQty > 0 ? $rowCost / $rowQty : 0;
            $qtyFromRow  = min($remaining, $rowQty);
            $amtFromRow  = $qtyFromRow * $unitCost;

            $dispatchItem              = new DispatchItem;
            $dispatchItem->dispatch_id = $dispatch->id;
            $dispatchItem->product_id  = $product->id;
            $dispatchItem->qty         = $qtyFromRow;
            $dispatchItem->unit_cost   = $unitCost;
            $dispatchItem->amount      = $amtFromRow;
            $dispatchItem->currency_id = $this->company->currency_id;

            if ($department === 'inventory') {
                $dispatchItem->inventory_id = $item->id;
            } elseif ($department === 'tyre') {
                $dispatchItem->tyre_id = $item->id;
            } elseif ($department === 'asset') {
                $dispatchItem->asset_id = $item->id;
            }

            $dispatchItem->save();

            $lineTotal += $amtFromRow;
            $remaining -= $qtyFromRow;
        }

        return $lineTotal;
    }

    /**
     * expenses item: "ProductName*qty:unit_price:TaxName"
     * TaxName is optional.
     */
    protected function createExpenseItem(Dispatch $dispatch, string $segment): float
    {
        // Parse: ProductName*qty:unit_price[:TaxName]
        if (!str_contains($segment, '*')) {
            return 0.0;
        }

        [$productPart, $rest] = explode('*', $segment, 2);
        $productName = trim($productPart);

        $restParts = explode(':', $rest, 3);
        $qty        = (float) trim($restParts[0] ?? 0);
        $unitPrice  = (float) trim($restParts[1] ?? 0);
        $taxName    = trim($restParts[2] ?? '');

        if ($qty <= 0 || blank($productName)) {
            return 0.0;
        }

        $product = Product::whereRaw('LOWER(name) = ?', [strtolower($productName)])->first();

        $taxRate   = 0.0;
        $taxId     = null;
        $taxAmount = 0.0;

        if (filled($taxName)) {
            [$taxId, $taxRate] = $this->resolveTax($taxName);
        }

        $subtotal     = $qty * $unitPrice;
        $taxAmount    = $subtotal * ($taxRate / 100);
        $subtotalIncl = $subtotal + $taxAmount;

        $dispatchItem                   = new DispatchItem;
        $dispatchItem->dispatch_id      = $dispatch->id;
        $dispatchItem->product_id       = $product?->id;
        $dispatchItem->currency_id      = $dispatch->currency_id;
        $dispatchItem->qty              = $qty;
        $dispatchItem->unit_cost        = $unitPrice;
        $dispatchItem->tax_id           = $taxId;
        $dispatchItem->tax_rate         = $taxRate ?: null;
        $dispatchItem->subtotal         = $subtotal;
        $dispatchItem->tax_amount       = $taxAmount ?: null;
        $dispatchItem->subtotal_incl    = $subtotalIncl;
        $dispatchItem->amount           = $subtotalIncl;
        $dispatchItem->save();

        return $subtotalIncl;
    }

    // ── Source rows (FIFO order) ───────────────────────────────────────────

    protected function getSourceRows(int $productId, string $department): \Illuminate\Support\Collection
    {
        return match ($department) {
            'tyre'      => Tyre::where('product_id', $productId)->where('status', 1)->where('balance', '>', 0)->orderBy('created_at', 'asc')->get(),
            'asset'     => Asset::where('product_id', $productId)->where('status', 1)->where('balance', '>', 0)->orderBy('created_at', 'asc')->get(),
            default     => Inventory::where('product_id', $productId)->where('status', 1)->where('balance', '>', 0)->orderBy('created_at', 'asc')->get(),
        };
    }

    // ── Dispatch number generator (mirrors Index component) ───────────────

    protected function dispatchNumber(): string
    {
        $str    = $this->company->name;
        $words  = explode(' ', $str);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];

        $latest = Dispatch::latest()->orderBy('id', 'desc')->first();
        $number = $latest ? $latest->id + 1 : 1;

        return $initials . 'D' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // ── Lookup / cache helpers ────────────────────────────────────────────

    protected function resolveCurrency(?string $code): int
    {
        if (blank($code)) {
            return $this->company->currency_id;
        }
        $key = strtoupper(trim($code));
        if (!isset($this->currencyCache[$key])) {
            $id = Currency::whereRaw('UPPER(code) = ?', [$key])->value('id');
            $this->currencyCache[$key] = $id ?? $this->company->currency_id;
        }
        return $this->currencyCache[$key];
    }

    protected function resolveStore(?string $name): ?int
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name));
        if (!isset($this->storeCache[$key])) {
            $this->storeCache[$key] = Store::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        return $this->storeCache[$key];
    }

    protected function resolveBranch(?string $name): ?int
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name));
        if (!isset($this->branchCache[$key])) {
            $this->branchCache[$key] = Branch::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        return $this->branchCache[$key];
    }

    protected function resolveDepartment(?string $name): ?int
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name));
        if (!isset($this->deptCache[$key])) {
            $this->deptCache[$key] = Department::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        return $this->deptCache[$key];
    }

    protected function resolveEmployee(?string $fullName): ?int
    {
        if (blank($fullName)) return null;
        $key = strtolower(trim($fullName));
        if (!isset($this->employeeCache[$key])) {
            $this->employeeCache[$key] = Employee::whereRaw("LOWER(CONCAT(name, ' ', surname)) = ?", [$key])->value('id');
        }
        return $this->employeeCache[$key];
    }

    protected function resolveVendor(?string $name): ?int
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name));
        if (!isset($this->vendorCache[$key])) {
            $vendor = Vendor::whereRaw('LOWER(name) = ?', [$key])->first()
                ?? Vendor::create(['name' => trim($name), 'status' => 1]);
            $this->vendorCache[$key] = $vendor->id;
        }
        return $this->vendorCache[$key];
    }

    protected function resolveProduct(?string $name, string $department): ?Product
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name)) . '|' . $department;
        if (!isset($this->productCache[$key])) {
            $this->productCache[$key] = Product::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
                ->where('department', $department)
                ->first();
        }
        return $this->productCache[$key];
    }

    protected function resolveTax(?string $name): array
    {
        if (blank($name)) return [null, 0.0];
        $key = strtolower(trim($name));
        if (!isset($this->taxCache[$key])) {
            $tax = Tax::whereRaw('LOWER(name) = ?', [$key])->first();
            $this->taxCache[$key] = $tax ? [$tax->id, (float) $tax->rate] : [null, 0.0];
        }
        return $this->taxCache[$key];
    }

    protected function resolveHorse(?string $reg): ?int
    {
        if (blank($reg)) return null;
        $key = strtolower(trim($reg));
        if (!isset($this->horseCache[$key])) {
            $this->horseCache[$key] = Horse::whereRaw('LOWER(registration_number) = ?', [$key])
                ->orWhereRaw('LOWER(fleet_number) = ?', [$key])
                ->value('id');
        }
        return $this->horseCache[$key];
    }

    protected function resolveTrailer(?string $reg): ?int
    {
        if (blank($reg)) return null;
        $key = strtolower(trim($reg));
        if (!isset($this->trailerCache[$key])) {
            $this->trailerCache[$key] = Trailer::whereRaw('LOWER(registration_number) = ?', [$key])
                ->orWhereRaw('LOWER(fleet_number) = ?', [$key])
                ->value('id');
        }
        return $this->trailerCache[$key];
    }

    protected function resolveVehicle(?string $reg): ?int
    {
        if (blank($reg)) return null;
        $key = strtolower(trim($reg));
        if (!isset($this->vehicleCache[$key])) {
            $this->vehicleCache[$key] = Vehicle::whereRaw('LOWER(registration_number) = ?', [$key])
                ->orWhereRaw('LOWER(fleet_number) = ?', [$key])
                ->value('id');
        }
        return $this->vehicleCache[$key];
    }

    // ── Validation ────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            '*.date'         => 'required|date',
            '*.dispatch_for' => 'required|in:inventory,expenses',
            '*.department'   => 'required|in:inventory,tyre,asset',
            '*.items'        => 'required|string',
        ];
    }
}
