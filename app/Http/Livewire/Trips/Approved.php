<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class Approved extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
      public function paginationView()
    { 
        return 'vendor.pagination.bootstrap-custom';
    }
  
    public $authorize;
    public $trailer_reg_numbers;
    public $comments;
    public $to;
    public $from;
    public $trip_filter;
    public $trip_id;
    public $search;
    public $perPage = 10;
    protected $queryString = [
        'search'                 => ['except' => ''],
        'trip_filter'            => ['except' => ''],
        'from'                   => ['except' => ''],
        'to'                     => ['except' => ''],
        'perPage'                => ['except' => 10],
        'page'                   => ['except' => 1],
    ];
        public $user;
    public $employee;
    public $employee_department;
    public $department_names;
    public $rank_names;
    public $role_names;
    public $company;


      public function clearFilters(): void
    {
        $this->search              = Null;
        $this->trip_filter         = 'created_at'; // ← reset to default, not ''
        $this->from                =  Null;
        $this->to                  = Null;
        $this->resetPage();

         $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Filters Cleared Successfully!!"
        ]);
    }

      public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingRangeFrom()
    {
        $this->resetPage();
    }

    public function updatingRangeTo()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function gotoPageNumber($page)
    {
        $page = (int) $page;

        if ($page > 0) {
            $this->gotoPage($page);
        }
    }
    

    public function getAuthorizer($id){
        if(is_null($id)){
            return ;
        }
        $user = User::find($id);
        return $user?->name." ".$user?->surname;
    }
    
    public function mount(){
          $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->employee_department = $this->employee->departments->first();
        $this->company = $this->employee->company;
         foreach($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
    
        foreach($this->user->roles as $role) {
            $this->role_names[] = $role->name;
        }
    
        foreach($this->employee->ranks as $rank) {
            $this->rank_names[] = $rank->name;
        }

        $this->resetPage();
        $this->trip_filter = 'updated_at';
      
      }

      public function authorize($id){
        $trip = Trip::find($id);
        $this->trip_id = $trip->id;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }
      public function dateRange(){
 
        // $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }


      public function update(){
        try {
         
        $trip = Trip::find($this->trip_id);
        $trip->authorized_by_id = Auth::user()->id;
        $trip->authorization = $this->authorize;
        $trip->reason = $this->comments;
        $trip->update();
        Session::flash('success','Authorization decision effected successfully');
        $this->dispatchBrowserEvent('hide-authorizationModal');
        if ($this->authorize == 'approved') {
            return redirect()->route('trips.approved');
        }else {
            return redirect()->route('trips.rejected');
        }
    }
    catch(\Exception $e){
    // Set Flash Message
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something goes wrong while updating trip!!"
    ]);
}
      }
    public function render()
    {
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.trips.approved',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )
                        ->where('trip_number','like', '%'.$this->search.'%')
                        ->orWhere('trip_status','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('delivery_note', function ($query) {
                            return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('user.employee', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('loading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('offloading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trip_documents', function ($query) {
                            return $query->where('document_number', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('updated_at','desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }else {
                    return view('livewire.trips.approved',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )->orderBy('updated_at','desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.trips.approved',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereMonth('updated_at', date('m'))
                    ->whereYear('updated_at', date('Y'))
                    ->where('trip_number','like', '%'.$this->search.'%')
                    ->orWhere('trip_status','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('delivery_note', function ($query) {
                        return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user.employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('loading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trip_documents', function ($query) {
                        return $query->where('document_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('offloading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('updated_at','desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
            }
            else {
               
                return view('livewire.trips.approved',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereMonth('updated_at', date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->orderBy('updated_at','desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
              
            }
        }else {
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.trips.approved',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)
                        ->where('trip_number','like', '%'.$this->search.'%')
                        ->orWhere('trip_status','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('delivery_note', function ($query) {
                            return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('user.employee', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trip_documents', function ($query) {
                            return $query->where('document_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('transporter', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('loading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('offloading_point', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('updated_at','desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }else{
                    return view('livewire.trips.approved',[
                        'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                        'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)->orderBy('updated_at','desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }
              
               
            }
            elseif (isset($this->search)) {
                return view('livewire.trips.approved',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->where('trip_number','like', '%'.$this->search.'%')->where('user_id',Auth::user()->id)
                    ->where('trip_number','like', '%'.$this->search.'%')
                    ->orWhere('trip_status','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('delivery_note', function ($query) {
                        return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user.employee', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trip_documents', function ($query) {
                        return $query->where('document_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('loading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('offloading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('updated_at','desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
            }
            else {
                
                return view('livewire.trips.approved',[
                    'trips' => Trip::query()->with(['customer:id,name' ,'transporter:id,name','horse','horse.horse_model','horse.horse_make',
                    'loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('authorization','approved')->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->where('user_id',Auth::user()->id)->orderBy('updated_at','desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);

            }

        }
   

        
       
    }
}
