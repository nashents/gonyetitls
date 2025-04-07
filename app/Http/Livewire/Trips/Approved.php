<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Approved extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

  
    public $authorize;
    public $trailer_reg_numbers;
    public $comments;
    public $to;
    public $from;
    public $trip_filter;
    public $trip_id;
    public $search;
    protected $queryString = ['search'];


    public function mount(){
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
