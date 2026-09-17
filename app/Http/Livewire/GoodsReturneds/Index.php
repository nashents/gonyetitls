<?php

namespace App\Http\Livewire\GoodsReturneds;

use App\Models\GoodsReturned;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $from;
    public $to;
    public $goods_returned_filter;
    public $goods_returned_number; // used in status modal display

    public $goods_returned_id;

    // Status update
    public $new_status;

    public $company;
    public $department;

    public function mount($department)
    {
        $this->goods_returned_filter = 'created_at';
        $this->company = Auth::user()->employee->company ?? null;
        $this->department = $department;
    }

    private function resetInputFields()
    {
        $this->new_status = '';
    }

    /**
     * Post-approval fulfilment stages only - draft/pending/approved/rejected
     * are exclusively controlled by the authorization workflow (CreateReturn
     * / GoodsReturnedAuthorizationService) and can't be jumped to from here.
     */
    public function showStatusModal($id)
    {
        $gr = GoodsReturned::findOrFail($id);

        if ($gr->authorization !== 'approved') {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Only an approved return can have its fulfilment status updated.',
            ]);
            return;
        }

        $this->goods_returned_id     = $id;
        $this->goods_returned_number = $gr->return_reference ?? $gr->goods_returned_number;
        $this->new_status            = $gr->status;
        $this->dispatchBrowserEvent('show-statusModal');
    }

    public function updateStatus()
    {
        $this->validate(['new_status' => 'required|in:dispatched_to_supplier,pending_replacement,replacement_received,refunded,credited,cancelled']);

        $gr = GoodsReturned::findOrFail($this->goods_returned_id);

        if ($gr->authorization !== 'approved') {
            $this->addError('new_status', 'Only an approved return can have its fulfilment status updated.');
            return;
        }

        $gr->update(['status' => $this->new_status]);

        $this->dispatchBrowserEvent('hide-statusModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Status Updated Successfully!',
        ]);
    }

    public function delete($id){
        $this->goods_returned_id = $id;
         $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){
        $gr = GoodsReturned::find($this->goods_returned_id);

        if ($gr && ! is_null($gr->authorization)) {
            $this->dispatchBrowserEvent('hide-deleteModal');
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Only a draft return (not yet submitted for approval) can be deleted.',
            ]);
            return;
        }

        $gr->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Goods Returned Record Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = GoodsReturned::query()
            ->with(['vendor', 'employee', 'user', 'goods_received', 'goods_returned_items'])
            ->where('department', $this->department);

        if ($this->from && $this->to) {
            $query->whereDate($this->goods_returned_filter, '>=', $this->from)
                  ->whereDate($this->goods_returned_filter, '<=', $this->to);
        } else {
            $query->whereMonth($this->goods_returned_filter, now()->month)
                  ->whereYear($this->goods_returned_filter, now()->year);
        }

        $query->when($this->search, function ($q) {
            $search = '%' . $this->search . '%';
            $q->where(function ($sub) use ($search) {
                $sub->where('goods_returned_number', 'like', $search)
                    ->orWhere('return_reference', 'like', $search)
                    ->orWhere('return_date', 'like', $search)
                    ->orWhere('return_type', 'like', $search)
                    ->orWhere('reason', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhereHas('vendor', fn($vq) => $vq->where('name', 'like', $search))
                    ->orWhereHas('employee', fn($eq) => $eq->whereRaw("concat(name, ' ', surname) like ?", [$search]));
            });
        });

        return view('livewire.goods-returneds.index', [
            'goods_returneds' => $query->orderBy($this->goods_returned_filter, 'desc')->paginate(10),
        ]);
    }
}
