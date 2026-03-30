<?php

namespace App\Http\Livewire\TransportOrders;


use App\Mail\AuthorizationNotificationMail;
use App\Models\Company;
use App\Models\TransportOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class Approved extends Component
{

    use WithPagination;

    public $selectedRows = [];
    public $selectPageRows = false;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $transport_order_id;
    public $authorize;
    public $comments;
    public $transport_order;
    public $transport_order_filter;
    public $user;
    public $employee;
    public $company;
    public $rank_names;
    public $role_names;
    public $department_names;



    public function mount(){
         $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->company = Company::with('currency')->find($this->employee->company_id);
        $this->resetPage();
        $this->transport_order_filter = "created_at";
          $departments = $this->employee->departments;
         foreach($departments as $department){
             $this->department_names[] = $department->name;
         }
         $roles = Auth::user()->roles;
         foreach($roles as $role){
             $this->role_names[] = $role->name;
         }
         $ranks = $this->employee->ranks;
         foreach($ranks as $rank){
             $this->rank_names[] = $rank->name;
         }
    }
   
    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->transport_orders->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

      
      public function authorizeSelectedRows(){

        $this->validate([
            'authorize' => 'required',
        ]);

        DB::transaction(function () {

        $selected_transport_orders = TransportOrder::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_transport_orders)) {

            foreach($selected_transport_orders as $transport_order){
                 
                $transport_order->authorized_by_id = Auth::user()->id;
                $transport_order->authorization = $this->authorize;
                $transport_order->authorization_date = now();
                $transport_order->comments = $this->comments;
                $transport_order->update();

                $company =  Auth::user()->employee->company;
                $user = $transport_order->user;
                $email = $user?->email ?? null;
                $notification = "transport_order Authorization";
                if($email){
                    Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $transport_order));
                }

             }
             if ($this->authorize == "approved") {
                $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Transport Order(s) Approved Successfully"
                ]);
                return redirect()->route('transport_orders.approved');
         }else {
            $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bulk Transport Order(s) Rejected Successfully"
            ]);
            return redirect()->route('transport_orders.rejected');
         }

         $this->reset(['selectedRows','selectPageRows']);
         
        }

    });
   }

   public function getTransportOrdersProperty(){

          $withRelations = [
            'trip_type:id,name',
            'customer:id,name',
            'loading_point:id,name',
            'offloading_point:id,name',
            'cargo:id,name,group,risk,type',
            'currency:id,name,symbol',
        ];

        $transport_orders = TransportOrder::query()->with($withRelations)->where('authorization','approved');

        /*
        |--------------------------------------------------------------------------
        | Search Logic
        |--------------------------------------------------------------------------
        */
        $applySearch = function ($query) {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('transport_order_number', 'like', "%{$search}%")
                    ->orWhere('authorization', 'like', "%{$search}%")
                    ->orWhereHas('trip_type', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('cargo', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereRaw("DATE_FORMAT(date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereHas('loading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('offloading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        };

      

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */
            if (filled($this->from) && filled($this->to)) {
                $transport_orders->whereBetween($this->transport_order_filter, [$this->from, $this->to]);
            } else {
                if (!filled($this->search)) {
                    $transport_orders->whereMonth($this->transport_order_filter, date('m'))
                        ->whereYear($this->transport_order_filter, date('Y'));
                }
            }

            if (filled($this->search)) {
                $applySearch($transport_orders);
            }

            return $transport_orders->orderBy($this->transport_order_filter, 'desc')->paginate(10);
   
   }
   public function getAuthorizer($id){
        if(is_null($id)){
            return ;
        }
        $user = User::find($id);
        return $user?->name." ".$user?->surname;
    }


    public function authorize($id){
        $transport_order = TransportOrder::find($id);
        $this->transport_order_id = $transport_order->id;
        $this->transport_order = $transport_order;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

          DB::transaction(function () {
    
            $transport_order = TransportOrder::find($this->transport_order_id);
            $transport_order->authorized_by_id = Auth::user()->id;
            $transport_order->authorization = $this->authorize;
            $transport_order->authorization_date = now();
            $transport_order->comments = $this->comments;
            $transport_order->update();

            $company =  Auth::user()->employee->company;
            $user = $transport_order->user;
            $email = $user?->email ?? null;
            $notification = "transport_order Authorization";
            if($email){
                Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $transport_order));
            }

        if ($this->authorize == "approved") {

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transport Order Approved Successfully"
            ]);
            return redirect()->route('transport_orders.approved');
            
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transport Order Rejected Successfully"
            ]);
            return redirect()->route('transport_orders.rejected');
        }

    });

      }

         
   
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        
        return view('livewire.transport-orders.approved',[
            'transport_orders' => $this->transport_orders,    
        ]);
   
    }
}
