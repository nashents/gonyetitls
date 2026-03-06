<?php

namespace App\Http\Livewire\Bookings;

use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthorizationNotificationMail;

class Pending extends Component
{

    use WithPagination;

    
    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    
    // private $bookings;
    public $booking_id;
    public $mechanic_id;
    public $authorize;
    public $comments;
    public $status;

    public $selectedRows = [];
    public $selectPageRows = false;


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

      public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->bookings->pluck('id')->map(function ($id){
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

        $selected_bookings = Booking::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_bookings)) {

             foreach($selected_bookings as $booking){
        
                $booking->authorized_by_id = Auth::user()->id;
                $booking->authorization = $this->authorize;
                $booking->authorization_date = now();
                $booking->comments = $this->comments;
                $booking->update();

                $company =  Auth::user()->employee->company;
                $user = $booking->user;
                $email = $user?->email ?? null;
                $notification = "Garage Booking Authorization";
                if($email){
                    Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $booking));
                }

                if ($this->authorize == "approved") {

                        $inspection = new Inspection;
                        $inspection->user_id = $booking->user_id;
                        $inspection->service_type_id = $booking->service_type_id;
                        $inspection->booking_id = $booking->id;
                        $inspection->horse_id = $booking->horse_id;
                        $inspection->asset_id = $booking->asset_id;
                        $inspection->vehicle_id = $booking->vehicle_id;
                        $inspection->trailer_id = $booking->trailer_id;
                        $inspection->inspection_number = $this->inspectionNumber();
                        $inspection->status = 1;
                        $inspection->save();
                        $inspection->employees()->syncWithoutDetaching($this->mechanic_id);

                        $ticket = new Ticket;
                        $ticket->user_id = $booking->user_id;
                        $ticket->booking_id = $booking->id;
                        $ticket->inspection_id = $inspection->id;
                        $ticket->service_type_id = $booking->service_type_id;
                        $ticket->vehicle_id = $booking->vehicle_id;
                        $ticket->horse_id = $booking->horse_id;
                        $ticket->asset_id = $booking->asset_id;
                        $ticket->trailer_id = $booking->trailer_id;
                        $ticket->in_date = $booking->in_date;
                        $ticket->in_time = $booking->in_time;
                        $ticket->ticket_number = $this->ticketNumber();
                        $ticket->odometer = $booking->odometer;
                        $ticket->hours = $booking->hours;
                        $ticket->station = $booking->station;
                        $ticket->status = 1;
                        $ticket->save();
                        $ticket->employees()->syncWithoutDetaching($this->mechanic_id);

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

            
                       
                         

                }
            }

            $this->reset(['selectedRows','selectPageRows']);

            if ($this->authorize == "approved") {
                $this->dispatchBrowserEvent('hide-bookingAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Booking(s) Approved Successfully !!"
                ]);
                return redirect()->route('bookings.approved');

            }else {
    
                $this->dispatchBrowserEvent('hide-bookingAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Booking(s) Rejected Successfully"
                ]);
                return redirect()->route('bookings.rejected');
            }
     
        }

    });

    }

      public function update(){

        DB::transaction(function () {

        $this->validate([
            'authorize' => 'required',
        ]);

        $booking = Booking::find($this->booking_id);
       
        $booking->authorized_by_id = Auth::user()->id;
        $booking->authorization = $this->authorize;
        $booking->authorization_date = now();
        $booking->comments = $this->comments;
        $booking->update();

        $company =  Auth::user()->employee->company;
        $user = $booking->user;
        $email = $user?->email ?? null;
        $notification = "Garage Booking Authorization";
        if($email){
            Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $booking));
        }

        if ($this->authorize == 'approved') {

        $inspection = new Inspection;
        $inspection->user_id = $booking->user_id;
        $inspection->service_type_id = $booking->service_type_id;
        $inspection->booking_id = $booking->id;
        $inspection->horse_id = $booking->horse_id;
        $inspection->asset_id = $booking->asset_id;
        $inspection->vehicle_id = $booking->vehicle_id;
        $inspection->trailer_id = $booking->trailer_id;
        $inspection->inspection_number = $this->inspectionNumber();
        $inspection->status = 1;
        $inspection->save();
        $inspection->employees()->syncWithoutDetaching($this->mechanic_id);

        $ticket = new Ticket;
        $ticket->user_id = $booking->user_id;
        $ticket->booking_id = $booking->id;
        $ticket->inspection_id = $inspection->id;
        $ticket->service_type_id = $booking->service_type_id;
        $ticket->vehicle_id = $booking->vehicle_id;
        $ticket->horse_id = $booking->horse_id;
        $ticket->asset_id = $booking->asset_id;
        $ticket->trailer_id = $booking->trailer_id;
        $ticket->in_date = $booking->in_date;
        $ticket->in_time = $booking->in_time;
        $ticket->ticket_number = $this->ticketNumber();
        $ticket->odometer = $booking->odometer;
        $ticket->hours = $booking->hours;
        $ticket->station = $booking->station;
        $ticket->status = 1;
        $ticket->save();
        $ticket->employees()->syncWithoutDetaching($this->mechanic_id);

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
        });
       
      }

       public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    
    public function getBookingsProperty()
    {
        $query = Booking::query()
            ->with(['ticket','inspection','horse','trailer','vehicle'])->where('authorization','pending');

        // Date window
        if ($this->from && $this->to) {
            $from = Carbon::parse($this->from)->startOfDay();
            $to   = Carbon::parse($this->to)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        } else {
            $query->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]);
        }


        // Search (GROUPED to avoid breaking previous filters)
        if (filled($this->search)) {
            $term = '%'.$this->search.'%';

            $query->where(function ($q) use ($term) {
                $q->where('booking_number', 'like', $term)
                ->orWhereHas('employees', function ($qq) use ($term) {
                    $qq->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                })
                ->orWhereHas('ticket', function ($qq) use ($term) {
                    $qq->where('ticket_number', 'like', $term);
                })
                ->orWhereHas('station', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                })
                ->orWhereHas('inspection', function ($qq) use ($term) {
                    $qq->where('inspection_number', 'like', $term);
                });
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function render()
    { 
         return view('livewire.bookings.pending',[
            'bookings' => $this->bookings
          
        ]);
      
    }
}
