<?php

namespace App\Http\Livewire\GoodsReceiveds;

use App\Models\GoodsReceived;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    protected $queryString = ['search'];
    public $goods_received_id;
    public $company;

    public function mount(){
        $this->company = Auth::user()->employee->company;
    }

    public function showRestore($id){
        $this->goods_received_id = $id;
        $this->dispatchBrowserEvent('show-goods_receivedRestoreModal');
    }

    public function restore(){
        $goods_received = GoodsReceived::onlyTrashed()->find($this->goods_received_id);

        if (! $goods_received) {
            $this->dispatchBrowserEvent('hide-goods_receivedRestoreModal');
            return;
        }

        // These were soft-deleted alongside the GRV (see GoodsReceivedController::destroy),
        // so bring them back together or the restored GRV would show zero items.
        $goods_received->inventories()->onlyTrashed()->restore();
        $goods_received->tyres()->onlyTrashed()->restore();
        $goods_received->assets()->onlyTrashed()->restore();
        $goods_received->restore();

        $this->dispatchBrowserEvent('hide-goods_receivedRestoreModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Goods Received Voucher Restored Successfully!!"
        ]);
    }

    public function render()
    {
        $base = GoodsReceived::onlyTrashed()
            ->with([
                'vendor', 'employee', 'user',
                'inventories' => fn ($q) => $q->withTrashed()->with('product'),
                'tyres' => fn ($q) => $q->withTrashed()->with('product'),
                'assets' => fn ($q) => $q->withTrashed()->with('product'),
            ]);

        $base->when(filled($this->from) && filled($this->to), function ($q) {
            $q->whereDate('deleted_at', '>=', $this->from)
                ->whereDate('deleted_at', '<=', $this->to);
        });

        // No default period restriction — deleted GRVs may be from any point in
        // time, and searching (e.g. by inventory/tyre/asset #) must find them
        // regardless of when they were received or deleted.
        $base->when(filled($this->search), function ($q) {
            $term = '%' . $this->search . '%';

            $q->where(function ($qq) use ($term) {
                $qq->where('goods_received_number', 'like', $term)
                    ->orWhere('department', 'like', $term)
                    ->orWhereHas('vendor', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    })
                    ->orWhereHas('inventories', function ($sub) use ($term) {
                        $sub->withTrashed()
                            ->where('inventory_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    })
                    ->orWhereHas('tyres', function ($sub) use ($term) {
                        $sub->withTrashed()
                            ->where('tyre_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    })
                    ->orWhereHas('assets', function ($sub) use ($term) {
                        $sub->withTrashed()
                            ->where('asset_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    });
            });
        });

        $goods_receiveds = $base
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('livewire.goods-receiveds.deleted', [
            'goods_receiveds' => $goods_receiveds,
        ]);
    }
}
