<?php

namespace App\Http\Livewire\Bookings;

use App\Models\Hour;
use App\Models\Horse;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Inspection;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    
    private $bookings;
    public $booking_id;
    public $mechanic_id;
    public $authorize;
    public $comments;
    public $status;


    public function mount(){
        
      }

      public function inspectionNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $inspection = Inspection::orderBy('id','desc')->first();

        if (!$inspection) {
            $inspection_number =  $initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $inspection->id + 1;
            $inspection_number =  $initials .'I'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $inspection_number;


    }
      public function ticketNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $ticket = Ticket::orderBy('id','desc')->first();

        if (!$ticket) {
            $ticket_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $ticket->id + 1;
            $ticket_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $ticket_number;


    }

      public function authorize($id){
        $booking = Booking::find($id);
        $this->booking_id = $booking->id;
        foreach ($booking->employees as $employee) {
            $this->mechanic_id[] = $employee->id;
        }
       
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
        $booking = Booking::find($this->booking_id);
        if ($booking->authorization == "approved") {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Booking  Approved Already"
            ]);
            }else {
        $booking->authorized_by_id = Auth::user()->id;
        $booking->authorization = $this->authorize;
        $booking->comments = $this->comments;
        $booking->update();

        if ($this->authorize == 'approved') {

        $inspection = new Inspection;
        $inspection->user_id = $booking->user_id;
        $inspection->service_type_id = $booking->service_type_id;
        $inspection->booking_id = $booking->id;
        $inspection->horse_id = $booking->horse_id;
        $inspection->vehicle_id = $booking->vehicle_id;
        $inspection->trailer_id = $booking->trailer_id;
        $inspection->inspection_number = $this->inspectionNumber();
        $inspection->status = 1;
        $inspection->save();
        $inspection->employees()->attach($this->mechanic_id);

        $ticket = new Ticket;
        $ticket->user_id = $booking->user_id;
        $ticket->booking_id = $booking->id;
        $ticket->inspection_id = $inspection->id;
        $ticket->service_type_id = $booking->service_type_id;
        $ticket->vehicle_id = $booking->vehicle_id;
        $ticket->horse_id = $booking->horse_id;
        $ticket->trailer_id = $booking->trailer_id;
        $ticket->in_date = $booking->in_date;
        $ticket->in_time = $booking->in_time;
        $ticket->ticket_number = $this->ticketNumber();
        $ticket->odometer = $booking->odometer;
        $ticket->hours = $booking->hours;
        $ticket->station = $booking->station;
        $ticket->status = 1;
        $ticket->save();
        $ticket->employees()->attach($this->mechanic_id);

        if(isset($booking->horse_id)){
            $horse = Horse::find($booking->horse_id);
            $horse->service = 1;
            $current_mileage  = $horse->mileage;
            $current_hours  = $horse->hours;
            if ($booking->odometer > $current_mileage ) {
                $horse->mileage = $booking->odometer;
            }
            if ($booking->hours > $current_hours ) {
                $horse->hours = $booking->hours;
            }
          
            $horse->update();
        }
        if(isset($booking->trailer_id)){
            $trailer = Trailer::find($booking->trailer_id);
            $trailer->service = 1;
            $current_mileage  = $trailer->mileage;
            $current_hours  = $trailer->hours;
            if ($booking->odometer > $current_mileage ) {
                $trailer->mileage = $booking->odometer;
            }
            if ($booking->hours > $current_hours ) {
                $trailer->hours = $booking->hours;
            }
           
            $trailer->update();
        }
        if(isset($booking->vehicle_id)){
            $vehicle = Vehicle::find($booking->vehicle_id);
            $vehicle->service = 1;
            $current_mileage  = $vehicle->mileage;
            if ($booking->odometer > $current_mileage ) {
                $vehicle->mileage = $booking->odometer;
            }
            $current_hours  = $vehicle->hours;
            if ($booking->hours > $current_hours ) {
                $vehicle->hours = $booking->odometer;
            }
          
            $vehicle->update();
        }

        $last_mileage = Mileage::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
        if(isset($last_mileage)){
            if($last_mileage < $booking->odometer){
                $mileage = new Mileage;
                $mileage->user_id = Auth::user()->id;
                $mileage->booking_id = $booking->id;
                $mileage->horse_id = $booking->horse_id;
                $mileage->trailer_id = $booking->trailer_id;
                $mileage->vehicle_id = $booking->vehicle_id;
                $mileage->mileage = $booking->odometer;
                $mileage->date = $booking->in_date;
                $mileage->category = "Garage Booking";
                $mileage->save();
            }
        }
        
        $last_hours = Hour::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
        if(isset($last_hours)){
            if($last_hours < $booking->hours){
                $hours = new Hour;
                $hours->user_id = Auth::user()->id;
                $hours->booking_id = $booking->id;
                $hours->horse_id = $booking->horse_id;
                $hours->trailer_id = $booking->trailer_id;
                $hours->vehicle_id = $booking->vehicle_id;
                $hours->hours = $booking->hours;
                $hours->date = $booking->in_date;
                $hours->category = "Booking";
                $hours->save();
            }
        }

        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Booking Approved Successfully"
        ]);
        return redirect()->route('bookings.approved');
        }
        else {

        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Booking Rejected Successfully"
        ]);
        return redirect()->route('bookings.rejected');

            }
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
                    return view('livewire.bookings.pending',[
                        'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )->where('authorization','pending')
                        ->where('booking_number','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('ticket', function ($query) {
                            return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('inspection', function ($query) {
                            return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })->orderBy('booking_number','desc')->paginate(10),
                      
                    ]);
                }else {
                    return view('livewire.bookings.pending',[
                        'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('authorization','pending')->whereBetween('created_at',[$this->from, $this->to] )->orderBy('booking_number','desc')->paginate(10),
                      
                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.bookings.pending',[
                    'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))
                    ->where('booking_number','like', '%'.$this->search.'%')
                    ->where('authorization','pending')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('ticket', function ($query) {
                        return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inspection', function ($query) {
                        return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })->orderBy('booking_number','desc')->paginate(10),
                  
                ]);
            }
            else {
               
                return view('livewire.bookings.pending',[
                    'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('authorization','pending')->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))->orderBy('booking_number','desc')->paginate(10),
                  
                ]);
              
            }
        }else {
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.bookings.pending',[
                        'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('authorization','pending')->whereBetween('created_at',[$this->from, $this->to] )->where('user_id',Auth::user()->id)
                        ->where('booking_number','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('ticket', function ($query) {
                            return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('inspection', function ($query) {
                            return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('trailer', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })->orderBy('booking_number','desc')->paginate(10),
                      
                    ]);
                }else{
                    return view('livewire.bookings.pending',[
                        'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('authorization','pending')->whereBetween('created_at',[$this->from, $this->to] )->where('user_id',Auth::user()->id)->orderBy('booking_number','desc')->paginate(10),
                      
                    ]);
                }
              
               
            }
            elseif (isset($this->search)) {
                return view('livewire.bookings.pending',[
                    'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('authorization','pending')->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))->where('user_id',Auth::user()->id)
                    ->where('booking_number','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('ticket', function ($query) {
                        return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inspection', function ($query) {
                        return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })->orderBy('booking_number','desc')->paginate(10),
                  
                ]);
            }
            else {
                
                return view('livewire.bookings.pending',[
                    'bookings' => Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->where('authorization','pending')->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))->where('user_id',Auth::user()->id)->orderBy('booking_number','desc')->paginate(10),
                  
                ]);

            }

        }
   
    }
}
