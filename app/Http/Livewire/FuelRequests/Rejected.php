<?php

namespace App\Http\Livewire\FuelRequests;

use App\Models\FuelRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Rejected extends Component
{
    use WithFileUploads;

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    protected $fuel_requests;
    public $authorize;
    public $comments;
    public $fuel_request_id;

    public $date;
    public $fullname;
    public $supplier;
    public $email;
    public $regnumber;
    public $authorized_by;
    public $fuel_type;
    public $quantity;
    public $driver;
    public $collection_point;
    public $delivery_point;
      public $from;
    public $to;
    public $fuel_request_filter;

    public function mount(){
        $this->fuel_request_filter = "created_at";
    }

    public function authorize($id){
        $fuel_request = FuelRequest::find($id);
        $this->fuel_request_id = $fuel_request->id;
        $this->dispatchBrowserEvent('show-fuelRequestAuthorizationModal');
    }

    public function update(){
        $fuel_request = FuelRequest::find($this->fuel_request_id);
        $fuel_request->authorized_by_id = Auth::user()->id;
        $fuel_request->authorization = $this->authorize;
        $fuel_request->authorization_date = now();
        $fuel_request->authorization_comments = $this->comments;
        $fuel_request->update();

        if ($fuel_request->allocation) {
            $allocation = $fuel_request->employee->allocation;
            $allocation->balance  =   $allocation->balance - $fuel_request->quantity;
            $allocation->update();
        }

       
      
        if ($this->authorize == "approved") {

            if ($fuel_request->from_allocation == True) {
                $this->email = $fuel_request->allocation?->container?->vendor?->email;
                $this->date = $fuel_request->date;
                $this->supplier = $fuel_request->employee->allocation->container->vendor->name;
                $this->driver = $fuel_request->employee->name .' '. $fuel_request->employee->surname;
                $this->fuel_type = $fuel_request->fuel_type;
                $this->quantity = $fuel_request->quantity;
                $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
                $this->regnumber = $fuel_request->employee->allocation->vehicle->registration_number;

                $data = array(
                    'email'=> $this->email,
                    'employee_email'=> $fuel_request->employee->email,
                    'date'=> $this->date,
                    'supplier'=> $this->supplier,
                    'driver'=> $this->driver,
                    'regnumber'=> $this->regnumber,
                    'authorized_by'=> $this->authorized_by,
                    'fuel_type'=> $this->fuel_type,
                    'quantity'=> $this->quantity,
                    'subject'=> 'Auto generated fuel request confirmation'

                );
                Mail::send('emails.fuel_requests',$data, function($message) use($data){
                    $message->to($data['email']);
                    $message->cc($data['employee_email']);
                    $message->from($data['from']);
                    $message->subject($data['subject']);
                });
            }

            $this->dispatchBrowserEvent('hide-fuelRequestAuthorizationModal');
            Session::flash('success','Fuel Request approved successfully');
            return redirect()->route('fuel_requests.approved');
        }else {
            $this->dispatchBrowserEvent('hide-fuelRequestAuthorizationModal');
            Session::flash('success','Fuel Request rejected successfully');
            return redirect()->route('fuel_requests.rejected');
        }
    

    }
    public function render()
    {
        $query = FuelRequest::query()
            ->with([
                'employee',
                'horse',
                'vehicle',
                'asset',
            ])
            ->where('authorization', 'rejected');

     
            $query->when(
                filled($this->from) && filled($this->to),
                function ($q) {
                    $q->whereBetween($this->fuel_request_filter, [
                        Carbon::parse($this->from)->startOfDay(),
                        Carbon::parse($this->to)->endOfDay(),
                    ]);
                },
                function ($q) {
                    $q->whereMonth($this->fuel_request_filter, now()->month)
                        ->whereYear($this->fuel_request_filter, now()->year);
                }
            );
      

        // Search
        $query->when($this->search, function ($q) {
            $search = '%' . trim($this->search) . '%';

            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('reason', 'like', $search)
                    ->orWhere('fuel_type', 'like', $search)
                    ->orWhereRaw('CAST(quantity AS CHAR) LIKE ?', [$search])

                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('name', 'like', $search)
                            ->orWhere('surname', 'like', $search)
                            ->orWhere('employee_number', 'like', $search);
                    })

                    ->orWhereHas('horse', function ($horseQuery) use ($search) {
                        $horseQuery->where('registration_number', 'like', $search)
                            ->orWhere('fleet_number', 'like', $search)
                            ->orWhere('make', 'like', $search)
                            ->orWhere('model', 'like', $search);
                    })

                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('registration_number', 'like', $search)
                            ->orWhere('fleet_number', 'like', $search)
                            ->orWhere('make', 'like', $search)
                            ->orWhere('model', 'like', $search);
                    })

                    ->orWhereHas('asset', function ($assetQuery) use ($search) {
                        $assetQuery->where('name', 'like', $search)
                            ->orWhere('serial_number', 'like', $search)
                            ->orWhere('asset_number', 'like', $search);
                    });
            });
        });

        $fuel_requests = $query
            ->latest()
            ->paginate(10);

        return view('livewire.fuel-requests.rejected', [
            'fuel_requests' => $fuel_requests,
        ]);
    }
}
