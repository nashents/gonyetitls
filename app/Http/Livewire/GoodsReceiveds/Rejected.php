<?php

namespace App\Http\Livewire\GoodsReceiveds;

use App\Models\GoodsReceived;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Rejected extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    protected $queryString = ['search'];
    public $goods_received_filter = "created_at";
    public $company;

    public function mount(){
        $this->company = Auth::user()->employee->company;
    }

    public function render()
    {
        $base = GoodsReceived::query()->with(['vendor', 'employee', 'user', 'authorized_by', 'inventories.product', 'tyres.product', 'assets.product'])
            ->where('authorization', 'rejected');

        $base->when(filled($this->from) && filled($this->to), function ($q) {
            $q->whereDate($this->goods_received_filter, '>=', $this->from)
                ->whereDate($this->goods_received_filter, '<=', $this->to);
        }, function ($q) {
            // Skip the default "this month" restriction while searching so matches
            // outside the current period (e.g. by inventory/tyre/asset #) aren't hidden.
            if (! filled($this->search)) {
                $q->whereMonth($this->goods_received_filter, Carbon::now()->month)
                    ->whereYear($this->goods_received_filter, Carbon::now()->year);
            }
        });

        $base->when(filled($this->search), function ($q) {
            $term = '%' . $this->search . '%';

            $q->where(function ($qq) use ($term) {
                $qq->where('goods_received_number', 'like', $term)
                    ->orWhere('department', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('vendor', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    })
                    ->orWhereHas('inventories', function ($sub) use ($term) {
                        $sub->where('inventory_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    })
                    ->orWhereHas('tyres', function ($sub) use ($term) {
                        $sub->where('tyre_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    })
                    ->orWhereHas('assets', function ($sub) use ($term) {
                        $sub->where('asset_number', 'like', $term)
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
            ->orderByDesc($this->goods_received_filter)
            ->paginate(10);

        return view('livewire.goods-receiveds.rejected', [
            'goods_receiveds' => $goods_receiveds,
        ]);
    }
}
