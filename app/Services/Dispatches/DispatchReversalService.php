<?php

namespace App\Services\Dispatches;

use App\Models\Asset;
use App\Models\Bill;
use App\Models\Dispatch;
use App\Models\Inventory;
use App\Models\Movement;
use App\Models\TicketExpense;
use App\Models\TicketInventory;
use App\Models\Tyre;
use App\Models\TyreAssignment;
use App\Services\Accounting\BillDeletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchReversalService
{
    public function __construct(protected BillDeletionService $billDeletion)
    {
    }

    /**
     * Undo everything an approved dispatch created: give back the stock/asset/tyre
     * balance it deducted, delete the bill + bill expenses it raised, and delete
     * the ticket inventory/expense/tyre-assignment rows it created. Leaves the
     * dispatch itself marked authorization=rejected, with a separate reversal trail.
     */
    public function reverse(Dispatch $dispatch, string $reason, ?int $reversedById = null): Dispatch
    {
        return DB::transaction(function () use ($dispatch, $reason, $reversedById) {

            $dispatch = Dispatch::with('dispatch_items')->lockForUpdate()->find($dispatch->id);

            if (! $dispatch) {
                throw ValidationException::withMessages([
                    'dispatch' => 'This dispatch could not be found.',
                ]);
            }

            if ($dispatch->authorization !== 'approved') {
                throw ValidationException::withMessages([
                    'dispatch' => 'Only approved dispatches can be reversed.',
                ]);
            }

            if ($dispatch->reversed_at) {
                throw ValidationException::withMessages([
                    'dispatch' => 'This dispatch has already been reversed.',
                ]);
            }

            foreach ($dispatch->dispatch_items as $dispatch_item) {

                if ($dispatch_item->inventory_id) {
                    $item = Inventory::lockForUpdate()->find($dispatch_item->inventory_id);
                } elseif ($dispatch_item->asset_id) {
                    $item = Asset::lockForUpdate()->find($dispatch_item->asset_id);
                } elseif ($dispatch_item->tyre_id) {
                    $item = Tyre::lockForUpdate()->find($dispatch_item->tyre_id);
                } else {
                    $item = null;
                }

                if ($item) {
                    $item->balance = (float) $item->balance + (float) $dispatch_item->qty;

                    if ($item->balance > 0 && (int) $item->status === 0) {
                        $item->status = 1;
                    }

                    $item->save();
                }

                // Undo the tyre fitment created on approval (department == 'tyre')
                Movement::where('dispatch_item_id', $dispatch_item->id)
                    ->whereNotNull('tyre_assignment_id')
                    ->get()
                    ->each(function ($movement) {
                        $assignmentId = $movement->tyre_assignment_id;
                        $movement->delete();
                        if ($assignmentId) {
                            TyreAssignment::where('id', $assignmentId)->delete();
                        }
                    });

                // Undo the ticket inventory line created on approval (dispatch_for == 'inventory')
                TicketInventory::where('dispatch_item_id', $dispatch_item->id)
                    ->get()
                    ->each(fn ($ticket_inventory) => $ticket_inventory->delete());

                $dispatch_item->status = 'returned';
                $dispatch_item->save();
            }

            // Ticket expenses are only linked at dispatch level (dispatch_for == 'expenses')
            TicketExpense::where('dispatch_id', $dispatch->id)
                ->get()
                ->each(fn ($ticket_expense) => $ticket_expense->delete());

            // Undo the bill + bill expenses raised on approval
            Bill::where('dispatch_id', $dispatch->id)
                ->get()
                ->each(fn ($bill) => $this->billDeletion->delete($bill, $reversedById ?? Auth::id(), $reason));

            $dispatch->authorization = 'rejected';
            $dispatch->reversed_by_id = $reversedById ?? Auth::id();
            $dispatch->reversed_at = now();
            $dispatch->reversal_comments = $reason;
            $dispatch->save();

            return $dispatch;
        });
    }
}
