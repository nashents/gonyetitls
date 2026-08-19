<?php

namespace App\Http\Livewire\GoodsReceiveds;

use App\Models\Employee;
use App\Models\GoodsReceived;
use App\Models\Purchase;
use App\Models\Vendor;
use App\Services\Sage\SageIntegration;
use App\Services\Sage\SageSyncService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $searchPurchase;
    protected $queryString = ['search','searchPurchase'];
    public $from;
    public $to;
    public $goods_received_filter;
    public $goods_received_number;

    private $goods_receiveds;
    public $goods_received;
    public $goods_received_id;
    public $purchases;
    public $purchase_id;
    public $vendors;
    public $vendor_id;
    public $employees;
    public $employee_id;
    public $date;
    public $delivery_date;
    public $driver_name;
    public $delivery_number;
    public $items;
    public $total;
    public $condition;
    public $comments;
    public $company;
    public $department;
    public $attach = False;

    public function mount($department){
        $this->goods_received_filter = "created_at";
        $this->company = Auth::user()->employee->company;
        $this->department = $department;
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
       
    }

        public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'vendor_id' => 'required',
        'employee_id' => 'required',
        'date' => 'required',
    ];

    private function resetInputFields(){
        $this->vendor_id = Null;
        $this->employee_id = Null;
        $this->date = Null;
        $this->driver_name = Null;
        $this->delivery_date = Null;
        $this->delivery_number = Null;
        $this->comments = Null;
        $this->condition = Null;
        $this->purchase_id = Null;
        $this->attach = False;
    }

    public function updatedAttach($value){
        if($value == False){
            $this->purchase_id = NUll;
        }
    }

   

     public function goodsReceivedNumber(){

     if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
 
        $goods_received = GoodsReceived::orderBy('id','desc')->first();

        if (!$goods_received) {
            $goods_received_number =  $initials .'GR'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $goods_received->id + 1;
            $goods_received_number =  $initials .'GR'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $goods_received_number;

    }

    public function store(){

        DB::transaction(function () {
            $goods_received = new GoodsReceived;
            $goods_received->goods_received_number = $this->goodsReceivedNumber();
            $goods_received->user_id = Auth::user()->id;
            $goods_received->vendor_id = $this->vendor_id;
            $goods_received->purchase_id = $this->purchase_id;
            $goods_received->department = $this->department;
            $goods_received->employee_id = $this->employee_id;
            $goods_received->date = $this->date;
            $goods_received->delivery_date = $this->delivery_date;
            $goods_received->delivery_number = $this->delivery_number;
            $goods_received->driver_name = $this->driver_name;
            $goods_received->condition = $this->condition;
            $goods_received->comments = $this->comments;
            $goods_received->authorization = 'pending';
            $goods_received->save();

            $this->dispatchBrowserEvent('hide-goods_receivedModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Good Received Voucher Created Successfully!!"
            ]);
        });

    }

    public function edit($id){
        $this->goods_received_id = $id;
        $goods_received = GoodsReceived::find($id);
        $this->date = $goods_received->date;
        $this->attach = !is_null($goods_received->purchase_id) ? True : False;
        $this->delivery_date = $goods_received->delivery_date;
        $this->employee_id = $goods_received->employee_id;
        $this->purchase_id = $goods_received->purchase_id;
        $this->vendor_id = $goods_received->vendor_id;
        $this->delivery_number = $goods_received->delivery_number;
        $this->driver_name = $goods_received->driver_name;
        $this->comments = $goods_received->comments;
        $this->condition = $goods_received->condition;
        $this->dispatchBrowserEvent('show-goods_receivedEditModal');
    }

    public function update(){

        DB::transaction(function () {
            $goods_received = GoodsReceived::find($this->goods_received_id);
            $goods_received->vendor_id = $this->vendor_id;
            $goods_received->employee_id = $this->employee_id;
            $goods_received->purchase_id = $this->purchase_id;
            $goods_received->date = $this->date;
            $goods_received->delivery_date = $this->delivery_date;
            $goods_received->delivery_number = $this->delivery_number;
            $goods_received->driver_name = $this->driver_name;
            $goods_received->condition = $this->condition;
            $goods_received->comments = $this->comments;
            $goods_received->update();

            $this->dispatchBrowserEvent('hide-goods_receivedEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Goods Received Voucher Updated Successfully!!"
            ]);
        });
    }

     public function showClose($id){
        $goods_received = GoodsReceived::find($id);
        $this->goods_received_id = $id;
        $this->goods_received_number = $goods_received->goods_received_number;
        $this->dispatchBrowserEvent('show-grvCloseModal');
    }

    public function closeGRV(){
        $goods_received =  GoodsReceived::find($this->goods_received_id);
        $goods_received->status = False;
        $goods_received->update();
        $this->dispatchBrowserEvent('hide-grvCloseModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Goods Received Voucher Closed Successfully!!"
        ]);
    }

    public function updatedPurchaseId($id){
        if(is_null($id)){
            return;
        }
        $purchase = Purchase::find($id);
        $this->vendor_id = $purchase?->vendor_id;

    }


    /** Sage integration gate — controls the GRV receipt sync badge/button. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /**
     * Manually push a GRV to Sage as a "Receipt" (Shipping Receipt) — the receipt
     * converts the linked PO, with inventory / tyre / asset additions as its lines.
     * Only authorized (approved) GRVs sync — the service enforces the same gate.
     */
    public function syncGoodsReceivedToSage($id)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $gr = GoodsReceived::find($id);
        if (! $gr) {
            return;
        }

        if (strcasecmp((string) $gr->authorization, 'approved') !== 0) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'warning',
                'message' => 'Only authorized (approved) goods-received notes can be synced to Sage.',
            ]);
            return;
        }

        try {
            $result = app(SageSyncService::class)->syncGoodsReceived($gr);
            $ok     = ! empty($result['success']) && ! empty($result['external_id']);
            $this->dispatchBrowserEvent('alert', [
                'type'    => $ok ? 'success' : 'warning',
                'message' => $ok
                    ? 'GRV synced to Sage receipt (' . $result['external_id'] . ').'
                    : 'Sage sync: ' . ($result['error'] ?? 'could not sync this GRV.'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Sage goods-receipt manual push failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Sage sync failed for this GRV.']);
        }
    }

    public function render()
    {
        $query = Purchase::query()
        ->with(['vendor', 'booking', 'purchase_products', 'purchase_products.product'])
        ->where('department', $this->department)
        ->where('created_at', '>=', Carbon::now()->subMonth())->where('authorization','approved')
        ->where('status', 1);

        // 🔹 Search filter (properly grouped so ORs don't escape other conditions)
        if (filled($this->searchPurchase)) {
            $search = '%'.$this->searchPurchase.'%';

            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', $search)
                ->orWhere('date', 'like', $search)
                ->orWhere('description', 'like', $search)
                ->orWhereHas('vendor', function ($sub) use ($search) {
                    $sub->where('name', 'like', $search);
                })
                ->orWhereHas('booking', function ($sub) use ($search) {
                    $sub->where('booking_number', 'like', $search);
                })
                ->orWhereHas('purchase_products.product', function ($sub) use ($search) {
                    $sub->where('name', 'like', $search);
                });
            });
        }

        $this->purchases = $query
            ->orderBy('created_at', 'desc')
            ->get();

        $query = GoodsReceived::query()
            ->with(['vendor', 'employee', 'sageMapping'])
            ->where('department', $this->department);

        // Date filter
        if ($this->from && $this->to) {
            $query->whereDate($this->goods_received_filter, '>=', $this->from)
                ->whereDate($this->goods_received_filter, '<=', $this->to);
        } else {
            $query->whereMonth($this->goods_received_filter, now()->month)
                ->whereYear($this->goods_received_filter, now()->year);
        }


        // Search filter
        $query->when($this->search, function ($q) {
            $search = '%' . $this->search . '%';

            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('goods_received_number', 'like', $search)
                    ->orWhere('date', 'like', $search)
                    ->orWhere('delivery_date', 'like', $search)
                    ->orWhere('driver_name', 'like', $search)
                    ->orWhere('delivery_number', 'like', $search)
                    ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('name', 'like', $search);
                    })
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where(
                            DB::raw("concat(name, ' ', surname)"),
                            'like',
                            $search
                        );
                    });
            });
        });

        return view('livewire.goods-receiveds.index', [
            'goods_receiveds' => $query
                ->orderBy($this->goods_received_filter, 'desc')
                ->paginate(10),
        ]);
    }
}
