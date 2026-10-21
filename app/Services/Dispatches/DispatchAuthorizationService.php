<?php

namespace App\Services\Dispatches;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Inventory;
use App\Models\TicketExpense;
use App\Models\TicketInventory;
use App\Models\Tyre;
use App\Services\Accounting\InventoryJournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Approve / reject a pending dispatch - shared by the single Authorization
 * modal and Bulk Authorize on the pending dispatch lists.
 *
 * Approving raises the "Ticket Dispatch" bill (+ lines for inventory/tyre),
 * the ticket inventory/expense rows, takes the items out of stock, fits
 * tyres that have a position and posts the bill (InventoryJournalService).
 * Rejecting only stamps the header. Notification mail is the caller's job,
 * sent after this commits.
 *
 * $fitments (tyre dispatches): [dispatch_item_id => [fit_type, axle, position, serial]].
 * defaultFitments() gives what the dispatch already holds - used by bulk.
 */
class DispatchAuthorizationService
{
    public function __construct(private TyreDispatchFitmentService $fitment)
    {
    }

    /** The dispatch's own saved fitment per tyre line (no serials entered). */
    public function defaultFitments(Dispatch $dispatch): array
    {
        if ($dispatch->department != 'tyre') {
            return [];
        }

        $units = $this->fitment->unitsFor($dispatch);
        $fitments = [];
        foreach ($dispatch->dispatch_items()->whereNotNull('tyre_id')->get() as $item) {
            $fitments[$item->id] = [
                'fit_type' => $item->fit_type ?: (count($units) === 1 ? array_key_first($units) : null),
                'axle' => $item->axle,
                'position' => $item->position,
                'serial' => '',
            ];
        }

        return $fitments;
    }

    /** First reason a tyre dispatch can't be approved with these fitments, else null. */
    public function tyreProblem(Dispatch $dispatch, array $fitments): ?string
    {
        if ($dispatch->department != 'tyre') {
            return null;
        }

        $rows = [];
        foreach ($dispatch->dispatch_items()->whereNotNull('tyre_id')->get() as $item) {
            $f = $fitments[$item->id] ?? [];
            $rows[$item->id] = [
                'tyre_id' => $item->tyre_id,
                'fit_type' => $f['fit_type'] ?? null,
                'axle' => $f['axle'] ?? null,
                'position' => $f['position'] ?? null,
                'serial' => $f['serial'] ?? null,
            ];
        }

        $errors = $this->fitment->validate($rows, $this->fitment->unitsFor($dispatch));
        // A unit is only needed when tyres are being fitted on approval.
        $fitting = collect($rows)->contains(fn ($row) => filled($row['axle']) || filled($row['position']));
        if (isset($errors['fit_unit'])) {
            if ($fitting) {
                return 'This dispatch has no horse, trailer or vehicle to fit the tyres to. Clear the positions to approve without fitting.';
            }
            unset($errors['fit_unit']);
        }

        return $errors ? reset($errors) : null;
    }

    public function authorize(Dispatch $dispatch, string $decision, ?string $comments, int $userId, array $fitments = []): Dispatch
    {
        return DB::transaction(function () use ($dispatch, $decision, $comments, $userId, $fitments) {
            $dispatch = Dispatch::lockForUpdate()->find($dispatch->id);

            if (!$dispatch) {
                throw ValidationException::withMessages(['dispatch' => 'Dispatch not found.']);
            }
            if ($dispatch->authorization !== 'pending') {
                throw ValidationException::withMessages(['dispatch' => "Dispatch {$dispatch->dispatch_number} is no longer pending."]);
            }

            $dispatch->authorized_by_id = $userId;
            $dispatch->authorization = $decision;
            $dispatch->authorization_date = now();
            $dispatch->authorization_comments = $comments;
            $dispatch->save();

            if ($decision === 'approved') {
                $this->approve($dispatch, $userId, $fitments);
            }

            return $dispatch;
        });
    }

    private function approve(Dispatch $dispatch, int $userId, array $fitments): void
    {
        $account = Account::where('name', 'Repairs & Maintenance')->first();

        $bill = new Bill;
        $bill->user_id = $userId;
        $bill->bill_number = $this->billNumber();
        $bill->dispatch_id = $dispatch->id;
        $bill->ticket_id = $dispatch->ticket_id;
        $bill->employee_id = $dispatch->employee_id;
        $bill->branch_id = $dispatch->branch_id;
        $bill->department_id = $dispatch->department_id;
        $bill->trailer_id = $dispatch->trailer_id;
        $bill->vehicle_id = $dispatch->vehicle_id;
        $bill->vendor_id = $dispatch->vendor_id;
        $bill->horse_id = $dispatch->horse_id;
        $bill->bill_date = $dispatch->date;
        $bill->currency_id = $dispatch->currency_id;
        $bill->category = "Ticket Dispatch";
        $bill->authorization = 'approved';
        $bill->authorized_by_id = $userId;
        $bill->to_be_paid = false;
        if ($account) {
            $bill->account_id = $account->id;
            $bill->account_type_id = optional($account->account_type)->id;
        }
        $bill->total = $dispatch->total;
        $bill->balance = $dispatch->total;
        $bill->save();

        foreach ($dispatch->dispatch_items as $dispatch_item) {
            if ($dispatch->dispatch_for == "inventory") {
                $this->createTicketInventory($dispatch, $dispatch_item);
            } elseif ($dispatch->dispatch_for == "expenses") {
                $this->createTicketExpense($dispatch, $dispatch_item);
            }

            if (in_array($dispatch->department, ['inventory', 'tyre'])) {
                $expense = new BillExpense;
                $expense->bill_id = $bill->id;
                $expense->currency_id = $bill->currency_id;
                $expense->qty = 1;
                $expense->amount = $dispatch_item->amount;
                $expense->subtotal = $dispatch_item->amount;
                $expense->subtotal_incl = $dispatch_item->amount;
                $expense->inventory_id = $dispatch_item->inventory_id;
                $expense->tyre_id = $dispatch_item->tyre_id;
                $expense->asset_id = $dispatch_item->asset_id;
                $expense->exchange_amount = $dispatch_item->exchange_amount;
                $expense->exchange_rate = $dispatch_item->exchange_rate;
                if ($account) {
                    $expense->account_id = $account->id;
                    $expense->account_type_id = optional($account->account_type)->id;
                }
                $expense->save();
            }

            if ($dispatch_item->inventory_id) {
                $item = Inventory::find($dispatch_item->inventory_id);
            } elseif ($dispatch_item->asset_id) {
                $item = Asset::find($dispatch_item->asset_id);
            } elseif ($dispatch_item->tyre_id) {
                $item = Tyre::find($dispatch_item->tyre_id);
            } else {
                continue;
            }

            if (!$item) {
                continue;
            }

            // Reduce the balance / contents
            $item->balance = max(0, (float) $item->balance - (float) $dispatch_item->qty);
            if ($item->balance <= 0) {
                $item->status = 0;
            }
            $item->save();
        }

        if ($dispatch->department == "tyre") {
            $this->fitTyres($dispatch, $fitments);
        }

        app(InventoryJournalService::class)->postDispatchBill($bill);
    }

    private function fitTyres(Dispatch $dispatch, array $fitments): void
    {
        // Serials entered in the approval window for tyres that had none.
        $this->fitment->applySerials(collect($fitments)
            ->map(fn ($f, $itemId) => ['tyre_id' => optional(DispatchItem::find($itemId))->tyre_id, 'serial' => $f['serial'] ?? null])
            ->filter(fn ($row) => $row['tyre_id'])
            ->all());

        foreach ($dispatch->dispatch_items()->with('tyre')->whereNotNull('tyre_id')->get() as $item) {
            $f = $fitments[$item->id] ?? [];
            $item->fit_type = ($f['fit_type'] ?? null) ?: null;
            $item->axle = ($f['axle'] ?? null) ?: null;
            $item->position = ($f['position'] ?? null) ?: null;
            $item->save();

            // With a position it's fitted there. A ticket (job card) dispatch is fitted to the ticket's unit
            // even without one; the position is set later in Tyres > Edit. Otherwise it leaves stores unfitted.
            if (($item->axle && $item->position) || $dispatch->ticket_id) {
                $this->fitment->fit($dispatch, $item);
            }
        }
    }

    private function createTicketInventory(Dispatch $dispatch, DispatchItem $dispatch_item): void
    {
        $ticket_inventory = new TicketInventory;
        $ticket_inventory->dispatch_id = $dispatch->id;
        $ticket_inventory->dispatch_item_id = $dispatch_item->id;
        $ticket_inventory->ticket_id = $dispatch->ticket_id ?? null;
        $ticket_inventory->product_id = $dispatch_item->product_id ?? null;
        $ticket_inventory->inventory_id = $dispatch_item->inventory_id ?? null;
        $ticket_inventory->tyre_id = $dispatch_item->tyre_id ?? null;
        $ticket_inventory->vehicle_id = $dispatch->vehicle_id ?? null;
        $ticket_inventory->horse_id = $dispatch->horse_id ?? null;
        $ticket_inventory->trailer_id = $dispatch->trailer_id ?? null;
        $ticket_inventory->qty = $dispatch_item->qty;
        $ticket_inventory->currency_id = $dispatch_item->currency_id ?? null;
        $ticket_inventory->amount = $dispatch_item->amount;
        $ticket_inventory->exchange_amount = $dispatch_item->exchange_amount;
        $ticket_inventory->exchange_rate = $dispatch_item->exchange_rate;
        $ticket_inventory->save();
    }

    private function createTicketExpense(Dispatch $dispatch, DispatchItem $dispatch_item): void
    {
        $ticket_expense = new TicketExpense;
        $ticket_expense->dispatch_id = $dispatch->id;
        $ticket_expense->ticket_id = $dispatch->ticket_id;
        $ticket_expense->currency_id = $dispatch_item->currency_id;
        $ticket_expense->payment_method_id = $dispatch_item->payment_method_id;
        $ticket_expense->vendor_id = $dispatch->vendor_id;
        $ticket_expense->product_id = $dispatch_item->product_id;
        $ticket_expense->qty = $dispatch_item->qty;
        $ticket_expense->amount = $dispatch_item->amount;
        $ticket_expense->tax_rate = $dispatch_item->tax_rate;
        $ticket_expense->tax_id = $dispatch_item->tax_id;
        $ticket_expense->subtotal = $dispatch_item->subtotal;
        $ticket_expense->subtotal_incl = $dispatch_item->subtotal_incl;
        $ticket_expense->exchange_rate = $dispatch_item->exchange_rate;
        $ticket_expense->exchange_amount = $dispatch_item->exchange_amount;
        $ticket_expense->save();
    }

    private function billNumber(): string
    {
        $company = Auth::user()->company ?? optional(Auth::user()->employee)->company;
        $words = explode(' ', (string) optional($company)->name);
        $initials = ($words[0][0] ?? '').($words[1][0] ?? '');

        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;

        return $initials.'B'.str_pad($number, 5, "0", STR_PAD_LEFT);
    }
}
