<?php

namespace App\Http\Livewire\GoodsReceiveds;

use App\Models\Vendor;
use Livewire\Component;
use App\Models\Employee;
use Livewire\WithPagination;
use App\Models\GoodsReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $goods_received_filter;
    public $goods_received_number;

    private $goods_receiveds;
    public $goods_received;
    public $goods_received_id;
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
        $this->vendor_id = '';
        $this->employee_id = '';
        $this->date = '';
        $this->driver_name = '';
        $this->delivery_date = '';
        $this->delivery_number = '';
        $this->comments = '';
        $this->condition = '';
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
      
        $goods_received = new GoodsReceived;
        $goods_received->goods_received_number = $this->goodsReceivedNumber();
        $goods_received->user_id = Auth::user()->id;
        $goods_received->vendor_id = $this->vendor_id;
        $goods_received->department = $this->department;
        $goods_received->employee_id = $this->employee_id;
        $goods_received->date = $this->date;
        $goods_received->delivery_date = $this->delivery_date;
        $goods_received->delivery_number = $this->delivery_number;
        $goods_received->driver_name = $this->driver_name;
        $goods_received->condition = $this->condition;
        $goods_received->comments = $this->comments;
        $goods_received->save();

        $this->dispatchBrowserEvent('hide-goods_receivedModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Good Received Voucher Created Successfully!!"
        ]);

    }

    public function edit($id){
        $this->goods_received_id = $id;
        $goods_received = GoodsReceived::find($id);
        $this->date = $goods_received->date;
        $this->delivery_date = $goods_received->delivery_date;
        $this->employee_id = $goods_received->employee_id;
        $this->vendor_id = $goods_received->vendor_id;
        $this->delivery_number = $goods_received->delivery_number;
        $this->driver_name = $goods_received->driver_name;
        $this->comments = $goods_received->comments;
        $this->condition = $goods_received->condition;
        $this->dispatchBrowserEvent('show-goods_receivedEditModal');
    }

    public function update(){

        try{

        $goods_received = GoodsReceived::find($this->goods_received_id);
        $goods_received->vendor_id = $this->vendor_id;
        $goods_received->employee_id = $this->employee_id;
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

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating goods received voucher !!"
        ]);
    }
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


    public function render()
    {

               if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.goods-receiveds.index',[
                        'goods_receiveds' => GoodsReceived::query()->with('vendor','employee')
                          ->where('department',$this->department)
                        ->whereDate($this->goods_received_filter, '>=', $this->from)
                        ->whereDate($this->goods_received_filter, '<=', $this->to)
                        ->where('goods_received_number','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('delivery_date','like', '%'.$this->search.'%')
                        ->orWhere('driver_name','like', '%'.$this->search.'%')
                        ->orWhere('delivery_number','like', '%'.$this->search.'%')
                        ->orWhereHas('vendor', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->goods_received_filter,'desc')->paginate(10),
                       
    

                    ]);
                }else {
                    return view('livewire.goods-receiveds.index',[
                        'goods_receiveds' => GoodsReceived::query()->with('vendor','employee')
                          ->where('department',$this->department)
                        ->whereDate($this->goods_received_filter, '>=', $this->from)
                        ->whereDate($this->goods_received_filter, '<=', $this->to)
                        ->where('to_be_paid', True)
                        ->orderBy($this->goods_received_filter,'desc')->paginate(10),

                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.goods-receiveds.index',[
                    'goods_receiveds' => GoodsReceived::query()->with('vendor','employee')
                      ->where('department',$this->department)
                    ->whereMonth($this->goods_received_filter, date('m'))
                    ->whereYear($this->goods_received_filter, date('Y'))
                    ->where('goods_received_number','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('delivery_date','like', '%'.$this->search.'%')
                    ->orWhere('driver_name','like', '%'.$this->search.'%')
                    ->orWhere('delivery_number','like', '%'.$this->search.'%')
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->goods_received_filter,'desc')->paginate(10),
                ]);
            }
            else {
               
                return view('livewire.goods-receiveds.index',[
                    'goods_receiveds' => GoodsReceived::query()->with('vendor','employee')
                    ->where('department',$this->department)
                    ->whereMonth($this->goods_received_filter, date('m'))
                    ->whereYear($this->goods_received_filter, date('Y'))
                    ->orderBy($this->goods_received_filter,'desc')->paginate(10),
                ]);
              
            }
    }
}
