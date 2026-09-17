<?php

namespace App\Http\Livewire\GoodsReturneds;

use App\Models\GoodsReturned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Rejected extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $company;

    public function mount()
    {
        $this->company = Auth::user()->employee->company ?? null;
    }

    public function render()
    {
        $base = GoodsReturned::query()
            ->with(['vendor', 'employee', 'user', 'authorized_by', 'goods_received', 'goods_returned_items'])
            ->where('authorization', 'rejected');

        $base->when(filled($this->search), function ($q) {
            $term = '%' . $this->search . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('goods_returned_number', 'like', $term)
                    ->orWhere('return_reference', 'like', $term)
                    ->orWhere('department', 'like', $term)
                    ->orWhereHas('vendor', fn ($sub) => $sub->where('name', 'like', $term))
                    ->orWhereHas('employee', fn ($sub) => $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term));
            });
        });

        return view('livewire.goods-returneds.rejected', [
            'goods_returneds' => $base->orderByDesc('created_at')->paginate(10),
        ]);
    }
}
