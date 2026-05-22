<?php

namespace App\Http\Livewire\GoodsReturneds;

use App\Models\Employee;
use App\Models\GoodsReceived;
use App\Models\GoodsReturned;
use App\Models\Purchase;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $searchPurchase;
    protected $queryString = ['search', 'searchPurchase'];

    public $from;
    public $to;
    public $goods_returned_filter;
    public $goods_returned_number; // used in status modal display

    private $goods_returneds;
    public $goods_returned_id;

    // Collections
    public $purchases;
    public $vendors;
    public $employees;
    public $goods_receiveds;

    // Form fields
    public $purchase_id;
    public $goods_received_id;
    public $vendor_id;
    public $employee_id;
    public $return_type;
    public $return_date;
    public $expected_resolution_date;
    public $reason;
    public $total_return_value = 0;
    public $currency = 'USD';

    // Status update
    public $new_status;

    public $company;
    public $department;
    public $attach = false;

    public function mount($department)
    {
        $this->goods_returned_filter = 'created_at';
        $this->company = Auth::user()->employee->company;
        $this->department = $department;
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->employees = Employee::orderBy('name', 'asc')->orderBy('surname', 'asc')->get();
        $this->goods_receiveds = GoodsReceived::where('department', $department)
            ->orderBy('created_at', 'desc')->get();
    }

    public function updated($value)
    {
        $this->validateOnly($value);
    }

    protected $rules = [
        'vendor_id'          => 'required',
        'employee_id'        => 'required',
        'return_type'        => 'required|in:replacement,refund,credit_note',
        'return_date'        => 'required|date',
        'total_return_value' => 'required|numeric|min:0',
        'currency'           => 'required|max:3',
    ];

    private function resetInputFields()
    {
        $this->vendor_id               = '';
        $this->employee_id             = '';
        $this->purchase_id             = '';
        $this->goods_received_id       = '';
        $this->return_type             = '';
        $this->return_date             = '';
        $this->expected_resolution_date = '';
        $this->reason                  = '';
        $this->total_return_value      = 0;
        $this->currency                = 'USD';
        $this->attach                  = false;
        $this->new_status              = '';
    }

    public function goodsreturnedNumber()
    {
        if (isset($this->company)) {
            $words = explode(' ', $this->company->name);
            $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        }

        $last = GoodsReturned::orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return $initials . 'GR' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function generateReturnReference()
    {
        $last = GoodsReturned::orderBy('id', 'desc')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'GR-' . now()->year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            $gr = new GoodsReturned;
            $gr->goods_returned_number    = $this->goodsreturnedNumber();
            $gr->return_reference         = $this->generateReturnReference();
            $gr->user_id                  = Auth::id();
            $gr->vendor_id                = $this->vendor_id;
            $gr->purchase_id              = $this->purchase_id ?: null;
            $gr->goods_received_id        = $this->goods_received_id ?: null;
            $gr->employee_id              = $this->employee_id;
            $gr->department               = $this->department;
            $gr->return_type              = $this->return_type;
            $gr->return_date              = $this->return_date;
            $gr->expected_resolution_date = $this->expected_resolution_date ?: null;
            $gr->reason                   = $this->reason;
            $gr->total_return_value       = $this->total_return_value;
            $gr->currency                 = strtoupper($this->currency);
            $gr->status                   = 'draft';
            $gr->save();
        });

        $this->dispatchBrowserEvent('hide-goods_returnedModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Goods Returned Voucher Created Successfully!',
        ]);
    }

    public function edit($id)
    {
        $gr = GoodsReturned::findOrFail($id);
        $this->goods_returned_id        = $gr->id;
        $this->vendor_id                = $gr->vendor_id;
        $this->purchase_id              = $gr->purchase_id;
        $this->goods_received_id        = $gr->goods_received_id;
        $this->employee_id              = $gr->employee_id;
        $this->return_type              = $gr->return_type;
        $this->return_date              = $gr->return_date;
        $this->expected_resolution_date = $gr->expected_resolution_date;
        $this->reason                   = $gr->reason;
        $this->total_return_value       = $gr->total_return_value;
        $this->currency                 = $gr->currency;
        $this->attach                   = !is_null($gr->purchase_id);
        $this->dispatchBrowserEvent('show-goods_returnedEditModal');
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {
            GoodsReturned::findOrFail($this->goods_returned_id)->update([
                'vendor_id'               => $this->vendor_id,
                'purchase_id'             => $this->purchase_id ?: null,
                'goods_received_id'       => $this->goods_received_id ?: null,
                'employee_id'             => $this->employee_id,
                'return_type'             => $this->return_type,
                'return_date'             => $this->return_date,
                'expected_resolution_date'=> $this->expected_resolution_date ?: null,
                'reason'                  => $this->reason,
                'total_return_value'      => $this->total_return_value,
                'currency'                => strtoupper($this->currency),
            ]);
        });

        $this->dispatchBrowserEvent('hide-goods_returnedEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Goods Returned Voucher Updated Successfully!',
        ]);
    }

    public function showStatusModal($id)
    {
        $gr = GoodsReturned::findOrFail($id);
        $this->goods_returned_id     = $id;
        $this->goods_returned_number = $gr->return_reference ?? $gr->goods_returned_number;
        $this->new_status            = $gr->status;
        $this->dispatchBrowserEvent('show-statusModal');
    }

    public function updateStatus()
    {
        $this->validate(['new_status' => 'required|in:draft,approved,dispatched_to_supplier,pending_replacement,replacement_received,refunded,credited,cancelled']);

        GoodsReturned::findOrFail($this->goods_returned_id)->update(['status' => $this->new_status]);

        $this->dispatchBrowserEvent('hide-statusModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Status Updated Successfully!',
        ]);
    }

    public function updatedPurchaseId($id)
    {
        if (is_null($id)) return;
        $purchase = Purchase::find($id);
        $this->vendor_id = $purchase?->vendor_id;
    }

    public function delete($id){
        $this->goods_returned_id = $id;
         $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){
        $gr = GoodsReturned::find($this->goods_returned_id);
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
        $purchaseQuery = Purchase::query()
            ->with(['vendor', 'booking', 'purchase_products', 'purchase_products.product'])
            ->where('department', $this->department)
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->where('authorization', 'approved')
            ->where('status', 1);

        if (filled($this->searchPurchase)) {
            $search = '%' . $this->searchPurchase . '%';
            $purchaseQuery->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', $search)
                    ->orWhere('date', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhereHas('vendor', fn($s) => $s->where('name', 'like', $search))
                    ->orWhereHas('booking', fn($s) => $s->where('booking_number', 'like', $search))
                    ->orWhereHas('purchase_products.product', fn($s) => $s->where('name', 'like', $search));
            });
        }

        $this->purchases = $purchaseQuery->orderBy('created_at', 'desc')->get();

        $query = GoodsReturned::query()
            ->with(['vendor', 'employee', 'user', 'purchase', 'goods_received'])
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
