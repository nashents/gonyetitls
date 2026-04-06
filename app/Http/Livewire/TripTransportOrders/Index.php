<?php

namespace App\Http\Livewire\TripTransportOrders;

use App\Models\Company;
use App\Models\TripTransportOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $perPage = 10;
    public $search;
    public $from;
    public $to;

     public $rank_names;
    public $role_names;
    public $department_names;
    public $company;
    public $employee;
    public $user;
    protected $queryString = [
        'search'
    ];

    public function paginationView()
    { 
        return 'vendor.pagination.bootstrap-custom';
    }

    public function mount(){
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->company = Company::with('currency')->find($this->employee->company_id);
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
    
    public function render()
    {
        
         $withRelations = [
            'trip',
            'transport_order',
            'currency',
        ];

        $trip_transport_orders = TripTransportOrder::query()->with($withRelations);

        /*
        |--------------------------------------------------------------------------
        | Search Logic
        |--------------------------------------------------------------------------
        */
        $applySearch = function ($query) {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('tto_number', 'like', "%{$search}%");
            });
        };

       


        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */
            if (filled($this->from) && filled($this->to)) {
                $trip_transport_orders->whereBetween('created_at', [$this->from, $this->to]);
            } else {
                if (!filled($this->search)) {
                    $trip_transport_orders->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'));
                }
            }

            if (filled($this->search)) {
                $applySearch($trip_transport_orders);
            }

            $trip_transport_orders->orderBy('created_at', 'desc');

        return view('livewire.trip-transport-orders.index',[
            'trip_transport_orders' => $trip_transport_orders->paginate($this->perPage)
        ]);
    }
}
