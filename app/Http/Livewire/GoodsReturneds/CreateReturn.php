<?php

namespace App\Http\Livewire\GoodsReturneds;

use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Employee;
use App\Models\GoodsReceived;
use App\Models\GoodsReturned;
use App\Models\Vendor;
use App\Services\GoodsReturneds\ReturnableQuantityResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Pick an approved GRV, select which of its lines to return with a qty and
 * reason per line, and save as an editable draft. A draft
 * (authorization=null) can be freely edited until submitForApproval() locks
 * it by flipping authorization to 'pending'.
 */
class CreateReturn extends Component
{
    public $department;
    public $company;

    public $goods_receiveds = [];
    public $goods_received_id;
    public $goods_received;

    public $lines = [];

    public $vendor_id;
    public $employee_id;
    public $return_date;
    public $reason;
    public $currency = 'USD';

    public $vendors = [];
    public $employees = [];

    /** Keyed by line id: ['selected' => bool, 'qty_returned' => .., 'return_reason' => .., 'notes' => ..] */
    public $rows = [];

    public $goods_returned_id;

    public function mount($department, $goodsReturnedId = null)
    {
        $this->department = $department;
        $this->company = Auth::user()->employee->company ?? null;
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->employees = Employee::orderBy('name', 'asc')->orderBy('surname', 'asc')->get();

        $this->goods_receiveds = GoodsReceived::where('department', $department)
            ->where('authorization', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($goodsReturnedId) {
            $this->loadDraft($goodsReturnedId);
        }
    }

    private function loadDraft($id)
    {
        $goodsReturned = GoodsReturned::with('goods_returned_items')->findOrFail($id);

        if (! is_null($goodsReturned->authorization)) {
            $this->addError('goods_returned', 'This return has already been submitted and can no longer be edited.');
            return;
        }

        $this->goods_returned_id = $goodsReturned->id;
        $this->goods_received_id = $goodsReturned->goods_received_id;
        $this->vendor_id = $goodsReturned->vendor_id;
        $this->employee_id = $goodsReturned->employee_id;
        $this->return_date = $goodsReturned->return_date;
        $this->reason = $goodsReturned->reason;
        $this->currency = $goodsReturned->currency;

        $this->updatedGoodsReceivedId($this->goods_received_id);

        foreach ($goodsReturned->goods_returned_items as $item) {
            $lineId = $item->inventory_id ?: ($item->asset_id ?: $item->tyre_id);
            if (isset($this->rows[$lineId])) {
                $this->rows[$lineId]['selected'] = true;
                $this->rows[$lineId]['qty_returned'] = (float) $item->qty_returned;
                $this->rows[$lineId]['return_reason'] = $item->return_reason;
                $this->rows[$lineId]['notes'] = $item->notes;
            }
        }
    }

    public function updatedGoodsReceivedId($id)
    {
        $this->rows = [];
        $this->lines = [];

        if (! $id) {
            return;
        }

        $this->goods_received = GoodsReceived::with([
            'inventories.product', 'tyres.product', 'assets.product',
        ])->find($id);

        if (! $this->goods_received) {
            return;
        }

        if ($this->goods_received->inventories->isNotEmpty()) {
            $this->lines = $this->goods_received->inventories;
        } elseif ($this->goods_received->assets->isNotEmpty()) {
            $this->lines = $this->goods_received->assets;
        } elseif ($this->goods_received->tyres->isNotEmpty()) {
            $this->lines = $this->goods_received->tyres;
        }

        $this->vendor_id = $this->goods_received->vendor_id;

        foreach ($this->lines as $line) {
            $this->rows[$line->id] = [
                'selected' => false,
                'qty_returned' => 0,
                'return_reason' => '',
                'notes' => '',
                'returnable_qty' => ReturnableQuantityResolver::forLine($line, $this->goods_returned_id),
            ];
        }
    }

    private function selectedLines()
    {
        return collect($this->lines)->filter(fn ($line) => ! empty($this->rows[$line->id]['selected']));
    }

    private function goodsReturnedNumber()
    {
        $initials = 'GR';
        if ($this->company) {
            $words = explode(' ', $this->company->name);
            $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        }
        $last = GoodsReturned::orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;
        return $initials . 'GR' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    private function generateReturnReference()
    {
        $last = GoodsReturned::orderBy('id', 'desc')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'GR-' . now()->year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function saveDraft()
    {
        $this->validate([
            'goods_received_id' => 'required',
            'vendor_id' => 'required',
            'employee_id' => 'required',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $selected = $this->selectedLines();

        if ($selected->isEmpty()) {
            $this->addError('goods_returned', 'Select at least one item to return.');
            return;
        }

        $goodsReceived = GoodsReceived::find($this->goods_received_id);
        if (! $goodsReceived || $goodsReceived->authorization !== 'approved') {
            $this->addError('goods_returned', 'Returns can only be created against an approved GRV.');
            return;
        }

        $bill = Bill::where('goods_received_id', $goodsReceived->id)->first();

        try {
            DB::transaction(function () use ($selected, $bill) {
                $goodsReturned = $this->goods_returned_id
                    ? GoodsReturned::findOrFail($this->goods_returned_id)
                    : new GoodsReturned;

                $isNew = ! $this->goods_returned_id;

                $goodsReturned->user_id = $goodsReturned->user_id ?: Auth::id();
                $goodsReturned->goods_returned_number = $goodsReturned->goods_returned_number ?: $this->goodsReturnedNumber();
                $goodsReturned->return_reference = $goodsReturned->return_reference ?: $this->generateReturnReference();
                $goodsReturned->vendor_id = $this->vendor_id;
                $goodsReturned->goods_received_id = $this->goods_received_id;
                $goodsReturned->employee_id = $this->employee_id;
                $goodsReturned->department = $this->department;
                $goodsReturned->return_type = $goodsReturned->return_type ?: 'credit_note';
                $goodsReturned->return_date = $this->return_date;
                $goodsReturned->reason = $this->reason;
                $goodsReturned->currency = strtoupper($this->currency);
                $goodsReturned->status = 'draft';
                $goodsReturned->save();

                if (! $isNew) {
                    $goodsReturned->goods_returned_items()->delete();
                }

                foreach ($selected as $line) {
                    $row = $this->rows[$line->id];
                    $qty = (float) $row['qty_returned'];
                    $returnable = ReturnableQuantityResolver::forLine($line, $goodsReturned->id);

                    if ($qty <= 0 || $qty > $returnable) {
                        throw ValidationException::withMessages([
                            'goods_returned' => "Quantity for {$line->product?->name} must be between 0 and {$returnable}.",
                        ]);
                    }

                    if (empty($row['return_reason'])) {
                        throw ValidationException::withMessages([
                            'goods_returned' => "A return reason is required for {$line->product?->name}.",
                        ]);
                    }

                    $billExpenseId = null;
                    if ($bill) {
                        $billExpenseId = BillExpense::where('bill_id', $bill->id)
                            ->where('inventory_id', $line instanceof \App\Models\Inventory ? $line->id : null)
                            ->where('asset_id', $line instanceof \App\Models\Asset ? $line->id : null)
                            ->where('tyre_id', $line instanceof \App\Models\Tyre ? $line->id : null)
                            ->value('id');
                    }

                    $goodsReturned->goods_returned_items()->create([
                        'product_id' => $line->product_id,
                        'inventory_id' => $line instanceof \App\Models\Inventory ? $line->id : null,
                        'asset_id' => $line instanceof \App\Models\Asset ? $line->id : null,
                        'tyre_id' => $line instanceof \App\Models\Tyre ? $line->id : null,
                        'bill_expense_id' => $billExpenseId,
                        'return_reason' => $row['return_reason'],
                        'qty_returned' => $qty,
                        'unit_cost' => $line->qty > 0 ? ((float) $line->subtotal / (float) $line->qty) : (float) $line->subtotal,
                        'notes' => $row['notes'] ?: null,
                    ]);
                }

                $goodsReturned->total_return_value = $goodsReturned->goods_returned_items()->sum('total_value');
                $goodsReturned->save();

                $this->goods_returned_id = $goodsReturned->id;
            });
        } catch (ValidationException $e) {
            $this->addError('goods_returned', collect($e->errors())->flatten()->first());
            return;
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Draft return saved. You can keep editing it or submit it for approval.',
        ]);
    }

    public function submitForApproval()
    {
        if (! $this->goods_returned_id) {
            $this->addError('goods_returned', 'Save the draft before submitting it for approval.');
            return;
        }

        $goodsReturned = GoodsReturned::with('goods_returned_items')->findOrFail($this->goods_returned_id);

        if ($goodsReturned->goods_returned_items->isEmpty()) {
            $this->addError('goods_returned', 'Add at least one item before submitting.');
            return;
        }

        foreach ($goodsReturned->goods_returned_items as $item) {
            $line = $item->inventory ?: ($item->asset ?: $item->tyre);
            $returnable = $line ? ReturnableQuantityResolver::forLine($line, $goodsReturned->id) : 0;
            if ((float) $item->qty_returned > $returnable) {
                $this->addError('goods_returned', "Only {$returnable} unit(s) of {$item->product?->name} remain returnable.");
                return;
            }
        }

        $goodsReturned->authorization = 'pending';
        $goodsReturned->save();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Return submitted for approval.',
        ]);

        $route = match ($this->department) {
            'asset' => 'goods_returneds.assets',
            'tyre' => 'goods_returneds.tyres',
            default => 'goods_returneds.index',
        };

        return redirect()->route($route);
    }

    public function render()
    {
        return view('livewire.goods-returneds.create-return');
    }
}
