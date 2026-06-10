<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Employee;
use App\Models\Horse;
use App\Models\Inspection;
use App\Models\ProblemCategory;
use App\Models\Product;
use App\Models\ServiceType;
use App\Models\Store;
use App\Models\Ticket;
use App\Models\TicketExpense;
use App\Models\TicketInventory;
use App\Models\Trailer;
use App\Models\TyreAssignment;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * DispatchesImport
 *
 * Columns (in file order):
 *   date                 - required, Y-m-d
 *   dispatch_for         - required, "inventory" or "non_inventory"
 *   domain               - required, "inventory", "tyre", or "asset"
 *   requested_by         - optional, full name (resolves to requested_by_id)
 *   horse                - optional, registration_number or fleet_number
 *   trailer              - optional, registration_number or fleet_number
 *   vehicle              - optional, registration_number or fleet_number
 *   asset                - optional, asset_number or serial_number
 *   assigned_to          - optional, full name (resolves to employee_id)
 *   store_name           - optional
 *   branch_name          - optional
 *   department_name      - optional (asset department)
 *   currency             - optional, currency code e.g. USD (matches currencies.name)
 *   vendor_name          - optional (required for non_inventory; auto-created if missing)
 *   description          - optional
 *   items                - required
 *   problem_description  - optional; if filled, creates Booking → Inspection → Ticket
 *   service_type         - optional, used when creating booking/ticket
 *   problem_category     - optional, matched against problem_categories.name

 *   mechanic_name        - optional, synced to booking/inspection/ticket employees
 *
 * Items format:
 *   inventory:     "ProductName*qty:unit_price, ..."   (unit_price optional — zero cost if omitted)
 *   non_inventory: "ProductName*qty:unit_price, ..."   (unit_price incl. tax — no tax calculation done)
 */
class DispatchesImport implements ToCollection, WithHeadingRow, WithValidation, WithMultipleSheets
{
    use Importable;
    use SkipsErrors;
    use SkipsFailures;

    protected $company;
    protected array $assetCache         = [];
    protected array $currencyCache      = [];
    protected array $storeCache         = [];
    protected array $branchCache        = [];
    protected array $deptCache          = [];
    protected array $employeeCache      = [];
    protected array $vendorCache        = [];
    protected array $productCache       = [];
    protected array $horseCache         = [];
    protected array $trailerCache       = [];
    protected array $vehicleCache       = [];
    protected array $serviceTypeCache   = [];
    protected array $problemCatCache    = [];

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
    }

    // Only import the first sheet — ignore Instructions and _lists sheets
    public function sheets(): array
    {
        return [0 => $this];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Main handler
    // ─────────────────────────────────────────────────────────────────────

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {

            // Skip empty rows or rows without the required fields
            if (blank($row->get('dispatch_for')) || blank($row->get('items'))) {
                continue;
            }

            DB::transaction(function () use ($row) {

                $dispatchFor = strtolower(trim($row->get('dispatch_for') ?? 'inventory'));
                $domain      = strtolower(trim($row->get('domain')       ?? 'inventory'));

                $currencyId    = $this->resolveCurrency($row->get('currency'));
                $storeId       = $this->resolveStore($row->get('store_name'));
                $branchId      = $this->resolveBranch($row->get('branch_name'));
                $deptId        = $this->resolveDepartment($row->get('department_name'));
                $employeeId    = $this->resolveEmployee($row->get('assigned_to'));
                $requestedById = $this->resolveEmployee($row->get('requested_by'));
                $vendorId      = $this->resolveVendor($row->get('vendor_name'));

                // ── Resolve entity — only one allowed ─────────────────────
                $rawHorse   = $row->get('horse');
                $rawTrailer = $row->get('trailer');
                $rawVehicle = $row->get('vehicle');
                $rawAsset   = $row->get('asset');

                $filledCount = (int) filled($rawHorse)
                             + (int) filled($rawTrailer)
                             + (int) filled($rawVehicle)
                             + (int) filled($rawAsset);

                if ($filledCount > 1) {
                    throw new \Exception(
                        "Row has more than one entity (horse/trailer/vehicle/asset) filled. A dispatch can only be linked to one."
                    );
                }

                $horseId   = filled($rawHorse)   ? $this->resolveHorse($rawHorse)     : null;
                $trailerId = filled($rawTrailer)  ? $this->resolveTrailer($rawTrailer) : null;
                $vehicleId = filled($rawVehicle)  ? $this->resolveVehicle($rawVehicle) : null;
                $assetId   = filled($rawAsset)    ? $this->resolveAsset($rawAsset)     : null;

                $mileageAtService   = filled($row->get('mileage_at_service'))   ? (float) $row->get('mileage_at_service')   : null;
                $nextServiceMileage = filled($row->get('next_service_mileage')) ? (float) $row->get('next_service_mileage') : null;

                // ── Booking / Inspection / Ticket ─────────────────────────
                $ticketId           = null;
                $problemDescription = trim($row->get('problem_description') ?? '');

                if (filled($problemDescription)) {
                    $ticketId = $this->createBookingInspectionTicket(
                        $row, $horseId, $trailerId, $vehicleId, $assetId, $employeeId,
                        $mileageAtService, $nextServiceMileage
                    );
                }

                // ── Dispatch ──────────────────────────────────────────────
                $dispatch = new Dispatch;
                $dispatch->user_id            = Auth::id();
                $dispatch->dispatch_number    = $this->dispatchNumber();
                $dispatch->date               = $row->get('date');
                $dispatch->dispatch_for       = $dispatchFor;
                $dispatch->department         = $domain;
                $dispatch->description        = $row->get('description');
                $dispatch->currency_id        = $currencyId;
                $dispatch->store_id           = $storeId;
                $dispatch->branch_id          = $branchId;
                $dispatch->department_id      = $deptId;
                $dispatch->employee_id        = $employeeId;
                $dispatch->requested_by_id    = $requestedById;
                $dispatch->vendor_id          = $vendorId;
                $dispatch->horse_id           = $horseId;
                $dispatch->trailer_id         = $trailerId;
                $dispatch->vehicle_id         = $vehicleId;
                $dispatch->ticket_id          = $ticketId;
                $dispatch->expand             = false;
                $dispatch->save();

                // ── Items ─────────────────────────────────────────────────
                $total    = 0.0;
                $rawItems = $row->get('items') ?? '';
                $segments = array_filter(array_map('trim', explode(',', $rawItems)));

                foreach ($segments as $segment) {
                    if ($dispatchFor === 'inventory') {
                        $total += $this->createInventoryItem($dispatch, $segment, $domain);
                    } else {
                        $total += $this->createNonInventoryItem($dispatch, $segment, $domain);
                    }
                }

                // ── Authorize immediately ─────────────────────────────────
                $dispatch->total              = $total;
                $dispatch->authorized_by_id   = Auth::id();
                $dispatch->authorization      = 'approved';
                $dispatch->authorization_date = now();
                $dispatch->save();

                // ── Approval: Bill + TicketInventory/Expense ──────────────
                $this->approveDispatch($dispatch);
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Approval logic (mirrors Pending::update approved block)
    // ─────────────────────────────────────────────────────────────────────

    protected function approveDispatch(Dispatch $dispatch): void
    {
        $account = Account::where('name', 'Repairs & Maintenance')->first();

        $bill = new Bill;
        $bill->user_id          = Auth::id();
        $bill->bill_number      = $this->billNumber();
        $bill->dispatch_id      = $dispatch->id;
        $bill->ticket_id        = $dispatch->ticket_id;
        $bill->employee_id      = $dispatch->employee_id;
        $bill->branch_id        = $dispatch->branch_id;
        $bill->department_id    = $dispatch->department_id;
        $bill->trailer_id       = $dispatch->trailer_id;
        $bill->vehicle_id       = $dispatch->vehicle_id;
        $bill->vendor_id        = $dispatch->vendor_id;
        $bill->horse_id         = $dispatch->horse_id;
        $bill->bill_date        = $dispatch->date;
        $bill->currency_id      = $dispatch->currency_id;
        $bill->category         = 'Ticket Dispatch';
        $bill->authorization    = 'approved';
        $bill->authorized_by_id = Auth::id();
        $bill->to_be_paid       = false;

        if ($account) {
            $bill->account_id      = $account->id;
            $bill->account_type_id = optional($account->account_type)->id;
        }

        $bill->total   = $dispatch->total;
        $bill->balance = $dispatch->total;
        $bill->save();

        foreach ($dispatch->dispatch_items as $dispatch_item) {

            // Tyre assignment
            if ($dispatch->department === 'tyre' && $dispatch_item->tyre_id) {
                $this->createTyreAssignment($dispatch_item);
            }

            // Ticket inventory / expense
            if ($dispatch->dispatch_for === 'inventory') {
                $this->createTicketInventory($dispatch_item);
            } elseif ($dispatch->dispatch_for === 'non_inventory') {
                $this->createTicketExpense($dispatch_item);
            }

            // Bill expense for inventory/tyre departments
            if (in_array($dispatch->department, ['inventory', 'tyre'])) {
                $expense = new BillExpense;
                $expense->bill_id         = $bill->id;
                $expense->currency_id     = $bill->currency_id;
                $expense->qty             = 1;
                $expense->amount          = $dispatch_item->amount;
                $expense->subtotal        = $dispatch_item->amount;
                $expense->subtotal_incl   = $dispatch_item->amount;
                $expense->inventory_id    = $dispatch_item->inventory_id;
                $expense->tyre_id         = $dispatch_item->tyre_id;
                $expense->asset_id        = $dispatch_item->asset_id;
                $expense->exchange_amount = $dispatch_item->exchange_amount;
                $expense->exchange_rate   = $dispatch_item->exchange_rate;

                if ($account) {
                    $expense->account_id      = $account->id;
                    $expense->account_type_id = optional($account->account_type)->id;
                }

                $expense->save();
            }
        }
    }

    protected function createTyreAssignment(DispatchItem $dispatch_item): void
    {
        $dispatch   = $dispatch_item->dispatch;
        $ticket     = $dispatch?->ticket;
        $horse_id   = $ticket?->horse_id;
        $vehicle_id = $ticket?->vehicle_id;
        $trailer_id = $ticket?->trailer_id;

        $assignment          = new TyreAssignment;
        $assignment->user_id = Auth::id();
        $assignment->tyre_id = $dispatch_item->tyre_id;

        if ($horse_id) {
            $assignment->type       = 'Horse';
            $assignment->horse_id   = $horse_id;
            $assignment->vehicle_id = null;
            $assignment->trailer_id = null;
        } elseif ($trailer_id) {
            $assignment->type       = 'Trailer';
            $assignment->trailer_id = $trailer_id;
            $assignment->horse_id   = null;
            $assignment->vehicle_id = null;
        } elseif ($vehicle_id) {
            $assignment->type       = 'Vehicle';
            $assignment->vehicle_id = $vehicle_id;
            $assignment->horse_id   = null;
            $assignment->trailer_id = null;
        }

        $assignment->starting_odometer = $ticket?->odometer;
        $assignment->date_fitted       = $dispatch->date;
        $assignment->current_mileage   = $ticket?->odometer;
        $assignment->status            = 1;
        $assignment->save();
    }

    protected function createTicketInventory(DispatchItem $dispatch_item): void
    {
        $dispatch = $dispatch_item->dispatch;

        $ti                  = new TicketInventory;
        $ti->ticket_id       = $dispatch->ticket_id;
        $ti->product_id      = $dispatch_item->product_id;
        $ti->inventory_id    = $dispatch_item->inventory_id;
        $ti->tyre_id         = $dispatch_item->tyre_id;
        $ti->vehicle_id      = $dispatch->vehicle_id;
        $ti->horse_id        = $dispatch->horse_id;
        $ti->trailer_id      = $dispatch->trailer_id;
        $ti->qty             = $dispatch_item->qty;
        $ti->currency_id     = $dispatch_item->currency_id;
        $ti->amount          = $dispatch_item->amount;
        $ti->exchange_amount = $dispatch_item->exchange_amount;
        $ti->exchange_rate   = $dispatch_item->exchange_rate;
        $ti->save();
    }

    protected function createTicketExpense(DispatchItem $dispatch_item): void
    {
        $dispatch = $dispatch_item->dispatch;

        $te                    = new TicketExpense;
        $te->ticket_id         = $dispatch->ticket_id;
        $te->currency_id       = $dispatch_item->currency_id;
        $te->payment_method_id = $dispatch_item->payment_method_id;
        $te->vendor_id         = $dispatch->vendor_id;
        $te->product_id        = $dispatch_item->product_id;
        $te->qty               = $dispatch_item->qty;
        $te->amount            = $dispatch_item->amount;
        $te->subtotal          = $dispatch_item->subtotal;
        $te->subtotal_incl     = $dispatch_item->subtotal_incl;
        $te->exchange_rate     = $dispatch_item->exchange_rate;
        $te->exchange_amount   = $dispatch_item->exchange_amount;
        $te->save();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Booking → Inspection → Ticket
    // ─────────────────────────────────────────────────────────────────────

    protected function createBookingInspectionTicket(
        $row,
        ?int $horseId,
        ?int $trailerId,
        ?int $vehicleId,
        ?int $assetId,
        ?int $employeeId,
        ?float $mileageAtService = null,
        ?float $nextServiceMileage = null
    ): ?int {

        $serviceTypeId = $this->resolveServiceType($row->get('service_type'));
        $problemCatId  = $this->resolveProblemCategory($row->get('problem_category'));
        $mechanicId    = $this->resolveEmployee($row->get('mechanic_name'));
        $vendorId      = $this->resolveVendor($row->get('vendor_name'));
        $date          = $row->get('date');
        $odometer      = $mileageAtService;
        $description   = trim($row->get('problem_description') ?? '');

        // Determine entity type
        $type           = null;
        $model          = null;
        $transporter_id = null;

        if ($horseId) {
            $type           = 'Horse';
            $model          = Horse::find($horseId);
            $transporter_id = $model?->transporter_id;
        } elseif ($assetId) {
            $type           = 'Asset';
        } elseif ($trailerId) {
            $type           = 'Trailer';
            $model          = Trailer::find($trailerId);
            $transporter_id = $model?->transporter_id;
        } elseif ($vehicleId) {
            $type           = 'Vehicle';
            $model          = Vehicle::find($vehicleId);
            $transporter_id = $model?->transporter_id;
        }

        if (!$type) {
            return null;
        }

        // ── Booking ───────────────────────────────────────────────────────
        $booking                       = new Booking;
        $booking->booking_number       = $this->bookingNumber();
        $booking->user_id              = Auth::id();
        $booking->assigned_to          = $mechanicId ? 'Mechanic' : ($vendorId ? 'Vendor' : 'Mechanic');
        $booking->vendor_id            = $vendorId && !$mechanicId ? $vendorId : null;
        $booking->problem_category_id  = $problemCatId;
        $booking->transaction_type     = 'Workshop';
        $booking->horse_id             = $horseId;
        $booking->trailer_id           = $trailerId;
        $booking->vehicle_id           = $vehicleId;
        $booking->asset_id             = $assetId;
        $booking->transporter_id       = $transporter_id;
        $booking->employee_id          = $employeeId;
        $booking->odometer             = $odometer;
        $booking->in_date              = $date;
        $booking->in_time              = '08:00:00';
        $booking->type                 = $type;
        $booking->description          = $description;
        $booking->service_type_id      = $serviceTypeId;
        $booking->status               = 0;
        // next_service only applies to fleet entities, not assets
        if ($horseId || $trailerId || $vehicleId) {
            $booking->next_service = $nextServiceMileage;
        }
        $booking->save();

        if ($mechanicId) {
            $booking->employees()->syncWithoutDetaching([$mechanicId]);
        }

        // ── Inspection ────────────────────────────────────────────────────
        $inspection                      = new Inspection;
        $inspection->user_id             = Auth::id();
        $inspection->service_type_id     = $serviceTypeId;
        $inspection->booking_id          = $booking->id;
        $inspection->horse_id            = $horseId;
        $inspection->vehicle_id          = $vehicleId;
        $inspection->trailer_id          = $trailerId;
        $inspection->asset_id            = $assetId;
        $inspection->inspection_number   = $this->inspectionNumber();
        $inspection->status              = 1;
        $inspection->save();

        if ($mechanicId) {
            $inspection->employees()->syncWithoutDetaching([$mechanicId]);
        }

        // ── Ticket ────────────────────────────────────────────────────────
        $ticket                  = new Ticket;
        $ticket->user_id         = Auth::id();
        $ticket->booking_id      = $booking->id;
        $ticket->inspection_id   = $inspection->id;
        $ticket->service_type_id = $serviceTypeId;
        $ticket->horse_id        = $horseId;
        $ticket->vehicle_id      = $vehicleId;
        $ticket->trailer_id      = $trailerId;
        $ticket->asset_id        = $assetId;
        $ticket->in_date         = $date;
        $ticket->in_time         = '08:00:00';
        $ticket->ticket_number   = $this->ticketNumber();
        $ticket->odometer        = $odometer;
        $ticket->status          = 0;
        // next_service only applies to fleet entities, not assets
        if ($horseId || $trailerId || $vehicleId) {
            $ticket->next_service = $nextServiceMileage;
        }
        $ticket->save();

        if ($mechanicId) {
            $ticket->employees()->syncWithoutDetaching([$mechanicId]);
        }

        return $ticket->id;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Item creators
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Format: "ProductName*qty:unit_price"
     * unit_price is optional — zero cost recorded if omitted.
     * Unit price should be inclusive of any tax.
     */
    protected function createInventoryItem(Dispatch $dispatch, string $segment, string $domain): float
    {
        if (!str_contains($segment, '*')) {
            return 0.0;
        }

        [$productPart, $rest] = explode('*', $segment, 2);
        $productName = trim($productPart);

        $restParts = explode(':', $rest, 2);
        $qty       = (float) trim($restParts[0] ?? 0);
        $unitPrice = filled(trim($restParts[1] ?? '')) ? (float) trim($restParts[1]) : null;

        if ($qty <= 0 || blank($productName)) {
            return 0.0;
        }

        $product = $this->resolveProduct($productName, $domain);
        if (!$product) {
            return 0.0;
        }

        $resolvedUnitCost = $unitPrice ?? 0.0;
        $amount           = $qty * $resolvedUnitCost;

        $di              = new DispatchItem;
        $di->dispatch_id = $dispatch->id;
        $di->product_id  = $product->id;
        $di->qty         = $qty;
        $di->unit_cost   = $resolvedUnitCost;
        $di->amount      = $amount;
        $di->currency_id = $this->company->currency_id;
        $di->save();

        return $amount;
    }

    /**
     * Format: "ProductName*qty:unit_price"
     * unit_price should be inclusive of any tax.
     * Uses same resolveProduct — auto-creates product if not found.
     */
    protected function createNonInventoryItem(Dispatch $dispatch, string $segment, string $domain): float
    {
        if (!str_contains($segment, '*')) {
            return 0.0;
        }

        [$productPart, $rest] = explode('*', $segment, 2);
        $productName = trim($productPart);

        $restParts = explode(':', $rest, 2);
        $qty       = (float) trim($restParts[0] ?? 0);
        $unitPrice = (float) trim($restParts[1] ?? 0);

        if ($qty <= 0 || blank($productName)) {
            return 0.0;
        }

        $product  = $this->resolveProduct($productName, $domain);
        $subtotal = $qty * $unitPrice;

        $di               = new DispatchItem;
        $di->dispatch_id  = $dispatch->id;
        $di->product_id   = $product?->id;
        $di->currency_id  = $dispatch->currency_id;
        $di->qty          = $qty;
        $di->unit_cost    = $unitPrice;
        $di->subtotal     = $subtotal;
        $di->subtotal_incl = $subtotal;
        $di->amount       = $subtotal;
        $di->save();

        return $subtotal;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Number generators
    // ─────────────────────────────────────────────────────────────────────

    protected function billNumber(): string
    {
        $words    = explode(' ', $this->company->name);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        $latest   = Bill::latest()->orderBy('id', 'desc')->first();
        $number   = $latest ? $latest->id + 1 : 1;
        return $initials . 'B' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected function dispatchNumber(): string
    {
        $words    = explode(' ', $this->company->name);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        $latest   = Dispatch::latest()->orderBy('id', 'desc')->first();
        $number   = $latest ? $latest->id + 1 : 1;
        return $initials . 'D' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected function bookingNumber(): string
    {
        $words    = explode(' ', $this->company->name);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        $latest   = Booking::latest()->orderBy('id', 'desc')->first();
        $number   = $latest ? $latest->id + 1 : 1;
        return $initials . 'BK' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected function inspectionNumber(): string
    {
        $words    = explode(' ', $this->company->name);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        $latest   = Inspection::latest()->orderBy('id', 'desc')->first();
        $number   = $latest ? $latest->id + 1 : 1;
        return $initials . 'IN' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected function ticketNumber(): string
    {
        $words    = explode(' ', $this->company->name);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        $latest   = Ticket::latest()->orderBy('id', 'desc')->first();
        $number   = $latest ? $latest->id + 1 : 1;
        return $initials . 'TK' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Lookup / cache helpers
    // ─────────────────────────────────────────────────────────────────────

    protected function resolveCurrency(?string $code): int
    {
        if (blank($code)) return $this->company->currency_id;
        $key = strtoupper(trim($code));
        if (!isset($this->currencyCache[$key])) {
            // currencies table: name column stores the code (e.g. USD, ZWG)
            $id = Currency::whereRaw('UPPER(name) = ?', [$key])->value('id');
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

    protected function resolveProduct(?string $name, string $domain): ?Product
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name)) . '|' . strtolower($domain);
        if (!isset($this->productCache[$key])) {
            $existing = Product::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
                ->whereRaw('LOWER(department) = ?', [strtolower($domain)])
                ->first();

            $this->productCache[$key] = $existing ?? Product::create([
                'name'       => trim($name),
                'department' => $domain,
                'status'     => 1,
            ]);
        }
        return $this->productCache[$key];
    }

    protected function resolveServiceType(?string $name): ?int
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name));
        if (!isset($this->serviceTypeCache[$key])) {
            $this->serviceTypeCache[$key] = ServiceType::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        return $this->serviceTypeCache[$key];
    }

    protected function resolveProblemCategory(?string $name): ?int
    {
        if (blank($name)) return null;
        $key = strtolower(trim($name));
        if (!isset($this->problemCatCache[$key])) {
            $this->problemCatCache[$key] = ProblemCategory::whereRaw('LOWER(name) = ?', [$key])->value('id');
        }
        return $this->problemCatCache[$key];
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

    protected function resolveAsset(?string $identifier): ?int
    {
        if (blank($identifier)) return null;
        $key = strtolower(trim($identifier));
        if (!isset($this->assetCache[$key])) {
            $this->assetCache[$key] = Asset::whereRaw('LOWER(asset_number) = ?', [$key])
                ->orWhereRaw('LOWER(serial_number) = ?', [$key])
                ->value('id');
        }
        return $this->assetCache[$key];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            '*.date'         => 'nullable|date',
            '*.dispatch_for' => 'nullable|in:inventory,non_inventory',
            '*.domain'       => 'nullable|in:inventory,tyre,asset',
            '*.items'        => 'nullable|string',
        ];
    }

    public function prepareForValidation(array $data, int $index): array
    {
        $values = array_filter(array_values($data), fn($v) => filled($v));
        if (empty($values)) {
            return $data;
        }

        if (filled($data['date'] ?? null) || filled($data['items'] ?? null)) {
            if (blank($data['dispatch_for'] ?? null)) {
                $data['dispatch_for'] = 'inventory';
            }
            if (blank($data['domain'] ?? null)) {
                $data['domain'] = 'inventory';
            }
        }

        return $data;
    }
}
