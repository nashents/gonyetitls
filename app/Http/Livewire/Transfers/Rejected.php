<?php

namespace App\Http\Livewire\Transfers;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Transfer;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class Rejected extends Component
{

       use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    private $transfers;
    public $transfer_filter;
    public $transfer;
    public $transfer_id;
    public $department;
    public $authorize;
    public $comments;

     public function mount($department){
        $this->department = $department;
    }



    public function render()
    {
         $from   = filled($this->from) ? Carbon::parse($this->from)->startOfDay() : null;
            $to     = filled($this->to)   ? Carbon::parse($this->to)->endOfDay()   : null;
            $search = trim((string) ($this->search ?? ''));

            $query = Transfer::query()
                ->with([
                    'inventory.product.brand',
                    'tyre.product.brand',
                ])
                ->where('authorization','rejected')
                ->when($from && $to, fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]))
                ->when(!($from && $to), fn (Builder $q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
                ->when($search !== '', function (Builder $q) use ($search) {
                    // IMPORTANT: group OR conditions so they don't break your date filters
                    $q->where(function (Builder $qq) use ($search) {
                        $like = "%{$search}%";

                        $qq->where('date', 'like', $like)
                        ->orWhere('comments', 'like', $like)

                        ->orWhereHas('inventory', function (Builder $inv) use ($like) {
                            $inv->where(function (Builder $i) use ($like) {
                                $i->where('inventory_number', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('inventory.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('inventory.product.brand', fn (Builder $b) => $b->where('name', 'like', $like))

                        ->orWhereHas('tyre', function (Builder $t) use ($like) {
                            $t->where(function (Builder $tt) use ($like) {
                                $tt->where('tyre_number', 'like', $like)
                                    ->orWhere('width', 'like', $like)
                                    ->orWhere('diameter', 'like', $like)
                                    ->orWhere('aspect_ratio', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('tyre.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('tyre.product.brand', fn (Builder $b) => $b->where('name', 'like', $like));
                    });
                })
                ->orderByDesc('created_at');

            return view('livewire.transfers.rejected', [
                'transfers' => $query->paginate(10),
            ]);
    }
}
