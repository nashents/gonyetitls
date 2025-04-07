<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\TopUp;
use App\Models\Driver;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Container;
use App\Exports\FuelsExport;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;

class Manage extends Component
{

    use WithFileUploads;

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private $fuels;
    public $fuel_id;
    public $assets;
    public $categories;
    public $category_values;
    public $asset_id;
    public $trips;
    public $vehicle;
    public $currencies;
    public $currency_id;
    public $selectedVehicle;
    public $vehicles;
    public $employees;
    public $employee;
    public $employee_id;
    public $horse;
    public $horses;
    public $selectedHorse;
    public $drivers;
    public $driver;
    public $fuel_tank_capacity;
    public $driver_id;
    public $containers;
    public $selected_horse;
    public $horse_id;
    public $container;
    public $type = NULL;

    public $unit_price = 0;
    public $amount = 0;
    public $quantity = 0 ;
    public $mileage;
    public $date;
    public $fillup;
    public $invoice_number;
    public $fuel_consumption;
    public $distance;
    public $file;
    public $comments;

    public $user_id;
    public $selectedContainer;
    public $container_id;
    public $container_balance;
    public $selectedCategory;
    public $selectedCategoryValue;
    public $selectedTrip;

    
    public $search;
    protected $queryString = ['search'];
    public $fuel_filter;
    public $from;
    public $to;

    public function exportFuelsCSV(Excel $excel){

        return $excel->download(new FuelsExport($this->from, $this->to, $this->fuel_filter,$this->container_id), 'fuel_orders_' .time().'.csv', Excel::CSV);
    }
    public function exportFuelsPDF(Excel $excel){

        return $excel->download(new FuelsExport($this->from, $this->to, $this->fuel_filter,$this->container_id), 'fuel_orders_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportFuelsExcel(Excel $excel){
        return $excel->download(new FuelsExport($this->from, $this->to, $this->fuel_filter,$this->container_id), 'fuel_orders_' .time().'.xlsx');
    }

    public function mount(){

        $this->resetPage();

        $this->fuel_filter = "created_at";

        $this->horses = Horse::where('service',0)->orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::where('service',0)->orderBy('registration_number','asc')->get();
        $this->assets = Asset::latest()->get();
        $this->categories = Category::latest()->get();
        $this->category_values = collect();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->trips = Trip::with('horse','destination')->where('authorization','approved')->orderBy('created_at','desc')->take(100)->get();
        $this->containers = Container::where('balance','>',0)->orderBy('name','asc')->latest()->get();
        $this->drivers = Driver::latest()->get();

    }

    public function orderNumber(){

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

        $fuel = Fuel::orderBy('id','desc')->first();

        if (!$fuel) {
            $fuel_number =  $initials .'FO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $fuel->id + 1;
            $fuel_number =  $initials .'FO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $fuel_number;
    }



    public function topup($id){

        $fuel = Fuel::find($id);
        $this->fillup = 0;
        $this->type = $fuel->type;
        $this->user_id = $fuel->user_id;
        $this->selectedHorse = $fuel->horse_id;
        $this->employee_id = $fuel->employee_id;
        $this->selectedVehicle = $fuel->vehicle_id;
        $this->trips = Trip::orderBy('created_at','desc')->get();
        $this->selectedTrip = $fuel->trip_id;
        $this->asset_id = $fuel->asset_id;
        $this->driver_id = $fuel->driver_id;
        $this->type = $fuel->type;
        $this->fuel_id = $fuel->id;

        if ($this->type == "Horse") {
            $horse = Horse::find( $this->selectedHorse);
            $this->mileage = $horse->mileage;
        }elseif($this->type == "Vehicle"){
            $vehicle = Vehicle::find( $this->selectedVehicle);
            $this->mileage = $vehicle->mileage;
        }elseif($this->type == "Asset"){
            if ($fuel->asset_id) {
                $this->selectedCategory = Asset::find($fuel->asset_id)->category_id;
                $this->selectedCategoryValue = Asset::find($fuel->asset_id)->category_value_id;
            }
        }

        $this->dispatchBrowserEvent('show-fuelTopupModal');
    }

    public function storeTopup(){
        
        try{

            $fuel = new Fuel;
            $fuel->user_id = Auth::user()->id;
            $fuel->order_number = $this->orderNumber();
            $fuel->employee_id = $this->employee_id;
            $fuel->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : NULL;
            $fuel->horse_id = $this->selectedHorse ? $this->selectedHorse : NULL;
            $fuel->asset_id = $this->asset_id ? $this->asset_id : NULL;
    
            if (isset($this->selectedTrip)) {
                $trip = Trip::find($this->selectedTrip);
                $fuel->trip_id = $trip->id;
                $fuel->driver_id = $trip->driver_id ? $trip->driver_id : Null;
            }
          
            $fuel->container_id = $this->selectedContainer;
            $fuel->date = $this->date;
            $fuel->unit_price = $this->unit_price;
            $fuel->quantity = $this->quantity;
            $fuel->currency_id = $this->currency_id;
            $fuel->amount = $this->amount;
            $fuel->odometer = $this->mileage;
            $fuel->fillup = $this->fillup;
            $fuel->type = $this->type;
            $fuel->comments = $this->comments;
            $fuel->save();
          
            // $employees = Employee::whereHas('departments', function ($query) {
            //     return $query->where('name', '=', "Transport & Logistics");
            // })->whereHas('ranks', function ($query) {
            //     return $query->where('name', '=', 'Management');
            // })->orWhereHas('ranks', function ($query) {
            //     return $query->where('name', '=', 'Director');
            // })->get();
           
            
            // if ($employees->count()>0) {
             
            //     $category = "Fuel Order";
            //     foreach ($employees as $employee) {
            //         if ($employee->email) {
            //             $company = Auth::user()->employee->company;
            //             Mail::to($employee->email)->send(new PendingNotificationEmails($company, $category, $fuel->order_number));
            //         }
                   
            //     }
            // }

         

            $this->dispatchBrowserEvent('hide-fuelTopupModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Topup Created Successfully!!"
            ]);

            return redirect(request()->header('Referer'));
    }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-fuelTopupModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating a fuel topup!!"
        ]);
    }
    }

    public function updatedSelectedContainer($id)
    {
        if (!is_null($id) ) {
            $this->container = Container::find($id);
            $top_ups = TopUp::where('container_id',$id)->where('rate','!=', NULL)->where('rate','!=',"")->where('currency_id',$this->container->currency_id)->get();
            
            $topups_price_total = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('rate','!=',"")->where('rate', 'REGEXP', '^[0-9]+$')->where('currency_id',$this->container->currency_id)->get()->sum('rate');
            
            $topups_count = $top_ups->count();
            
            if ((isset($topups_count) && $topups_count > 0) && (isset($topups_price_total) && $topups_price_total > 0)) {
                $this->unit_price = number_format($topups_price_total/$topups_count,2);
            }
        
            $this->container_balance = $this->container ? $this->container->balance : "";
            $this->currency_id = $this->container->currency_id;
            }
    }

    

    public function updatedSelectedCategory($id)
    {
        if (!is_null($id) ) {
        $this->assets = Asset::where('category_id', $id)
        ->where('status', 1)->get();
        }
    }
    public function updatedSelectedTrip($id)
    {
        if (!is_null($id) ) {
        $trip = Trip::find($id);
        $this->horse = $trip->horse;
        $this->driver = $trip->driver;
        $this->selectedHorse = $this->horse->id;
        $this->selectedDriver =$this->driver->id;
        $this->distance = $trip->distance;
        $this->mileage = $this->horse->mileage;
        $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
        $this->fuel_consumption = $this->horse->fuel_consumption;
        // $this->quantity = $this->fuel_consumption * $this->distance;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedVehicle' => 'required',
        'selectedHorse' => 'required',
        // 'driver_id' => 'required',
        'selectedContainer' => 'required',
        // 'selectedTrip' => 'required',
        'selectedCategory' => 'required',
        'asset_id' => 'required',
        'date' => 'required',
        'mileage' => 'required',
        'quantity' => 'required',
        'amount' => 'required',
        'fillup' => 'required',
        'type' => 'required',
        'invoice_number' => 'nullable',
        'file' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'comments' => 'nullable',
    ];

    private function resetInputFields(){

        $this->selectedVehicle = "";
        $this->selectedHorse = "";
        $this->selectedTrip = "";
        $this->selectedContainer = "";
        $this->selectedCategory = "";
        $this->selectedCategoryValue = "";
        $this->asset_id = "";
        $this->date = "";
        $this->quantity = "";
        $this->unit_price = "";
        $this->mileage = "";
        $this->fillup = "";
        $this->type = "";
        $this->invoice_number = "";
    }

    public function edit($id){
        $fuel = Fuel::find($id);
        $this->user_id = $fuel->user_id;
        $this->selectedHorse = $fuel->horse_id;
        $this->employee_id = $fuel->employee_id;
        $this->selectedVehicle = $fuel->vehicle_id;
        $this->selectedTrip = $fuel->trip_id;
        $this->currency_id = $fuel->currency_id;
        $this->asset_id = $fuel->asset_id;
        $this->selectedContainer = $fuel->container_id;
        $this->container = Container::find($fuel->container_id);
        $this->container_balance = $this->container ? $this->container->balance : "";
        $this->driver_id = $fuel->driver_id;
        $this->date = $fuel->date;
        $this->fillup = $fuel->fillup;
        $this->type = $fuel->type;
        $this->mileage = $fuel->odometer;
        $this->comments = $fuel->comments;
        $this->amount = $fuel->amount;
        $this->unit_price = $fuel->unit_price;
        $this->quantity = $fuel->quantity;
        $this->fuel_id = $fuel->id;
        if ($fuel->asset_id) {
            $this->selectedCategory = Asset::find($fuel->asset_id)->category_id;
            $this->selectedCategoryValue = Asset::find($fuel->asset_id)->category_value_id;
        }
        $this->dispatchBrowserEvent('show-fuelEditModal');

    }


    public function update()
    {
        
        try{

            if ($this->fuel_id) {
    
                $trip = Trip::find($this->selectedTrip);
                $fuel = Fuel::find($this->fuel_id);
                $fuel->user_id = Auth::user()->id;
                $fuel->employee_id = $this->employee_id;
                $fuel->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : NULL;
                $fuel->horse_id = $this->selectedHorse ? $this->selectedHorse : NULL;
                $fuel->asset_id = $this->asset_id ? $this->asset_id : NULL;
    
                if (isset($trip)) {
                    $fuel->driver_id = $trip->driver_id;
                    $fuel->trip_id = $trip->id;
                }
               
                $fuel->container_id = $this->selectedContainer;
                $fuel->date = $this->date;
                $fuel->unit_price = $this->unit_price;
                $fuel->quantity = $this->quantity;
                $fuel->currency_id = $this->currency_id;
                $fuel->amount = $this->amount;
                $fuel->odometer = $this->mileage;
                $fuel->fillup = $this->fillup;
                $fuel->type = $this->type;
                $fuel->comments = $this->comments;
    
                $fuel->update();
    
                if (isset($this->selectedVehicle)) {
                    $vehicle = Vehicle::find($this->selectedVehicle);
                    $vehicle->mileage = $this->mileage;
                    $vehicle->update();
                }elseif(isset($this->selectedHorse)){
                    $horse = Horse::find($this->selectedHorse);
                    $horse->mileage = $this->mileage;
                    $horse->update();
                }
    
                $this->dispatchBrowserEvent('hide-fuelEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Fuel Order Updated Successfully!!"
                ]);
                return redirect(request()->header('Referer'));
    
    
            }
        }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-fuelEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while updating fuel order!!"
            ]);
        }
    }

    public function destroy($id)
    {
        if($id){
            Fuel::where('id',$id)->delete();
            session()->flash('message', 'Fuel Deleted Successfully.');
        }
    }

    public function dateRange(){
 
      
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
                  // sleep(1);

            if ($this->quantity != null && $this->unit_price != null) {
                $this->amount = $this->quantity * $this->unit_price;
            }      

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
                        return view('livewire.fuels.manage',[
                            'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make'
                            ])->whereBetween($this->fuel_filter,[$this->from, $this->to] )
                            ->where('order_number','like', '%'.$this->search.'%')
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('asset.brand', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('container', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orderBy('order_number','desc')->paginate(10),
                            'fuel_filter' => $this->fuel_filter,
                            'amount' => $this->amount,
                            'type' => $this->type,
                        ]);
                    }elseif(isset($this->container_id)){
                        return view('livewire.fuels.manage',[
                            'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                            ])->where('container_id',$this->container_id)->whereBetween($this->fuel_filter,[$this->from, $this->to] )->orderBy('order_number','desc')->paginate(10),
                            'fuel_filter' => $this->fuel_filter,
                            'amount' => $this->amount,
                            'type' => $this->type,

                        ]);
                    }else {
                        return view('livewire.fuels.manage',[
                            'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                            ])->whereBetween($this->fuel_filter,[$this->from, $this->to] )->orderBy('order_number','desc')->paginate(10),
                            'fuel_filter' => $this->fuel_filter,
                            'amount' => $this->amount,
                            'type' => $this->type,

                        ]);
                    }
                   
                }
                elseif (isset($this->search)) {
                   
                    return view('livewire.fuels.manage',[
                        'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                        ])->whereMonth('created_at', date('m'))
                        ->whereYear('created_at', date('Y'))
                        ->where('order_number','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('asset.brand', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('container', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('order_number','desc')->paginate(10),
                        'fuel_filter' => $this->fuel_filter,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
                }elseif(isset($this->container_id)){
                    return view('livewire.fuels.manage',[
                        'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                        ])->where('container_id',$this->container_id)->whereMonth($this->fuel_filter, date('m'))
                        ->whereYear($this->fuel_filter, date('Y'))->orderBy('order_number','desc')->paginate(10),
                        'fuel_filter' => $this->fuel_filter,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
                }
                else {
                   
                    return view('livewire.fuels.manage',[
                        'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                        ])->whereMonth($this->fuel_filter, date('m'))
                        ->whereYear($this->fuel_filter, date('Y'))->orderBy('order_number','desc')->paginate(10),
                        'fuel_filter' => $this->fuel_filter,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
                  
                }
            }else {
                if (isset($this->from) && isset($this->to)) {
                    if (isset($this->search)) {
                        return view('livewire.fuels.manage',[
                            'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                            ])->whereBetween($this->fuel_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)
                            ->where('order_number','like', '%'.$this->search.'%')
                            ->orWhereHas('horse', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('vehicle', function ($query) {
                                return $query->where('registration_number', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('asset.brand', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orWhereHas('container', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            })
                            ->orderBy('order_number','desc')->paginate(10),
                            'fuel_filter' => $this->fuel_filter,
                            'amount' => $this->amount,
                            'type' => $this->type,
                        ]);
                    }elseif(isset($this->container_id)){
                        return view('livewire.fuels.manage',[
                            'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                            ])->where('container_id',$this->container_id)->where('user_id',Auth::user()->id)->whereBetween($this->fuel_filter,[$this->from, $this->to] )->orderBy('order_number','desc')->paginate(10),
                            'fuel_filter' => $this->fuel_filter,
                            'amount' => $this->amount,
                            'type' => $this->type,

                        ]);
                    }
                    else{
                        return view('livewire.fuels.manage',[
                            'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make'
                            ])->whereBetween($this->fuel_filter,[$this->from, $this->to] )->where('user_id',Auth::user()->id)->orderBy('order_number','desc')->paginate(10),
                            'fuel_filter' => $this->fuel_filter,
                            'amount' => $this->amount,
                            'type' => $this->type,
                        ]);
                    }
                  
                   
                }
                elseif (isset($this->search)) {
                    return view('livewire.fuels.manage',[
                        'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                        ])->whereMonth($this->fuel_filter, date('m'))
                        ->whereYear($this->fuel_filter, date('Y'))->where('order_number','like', '%'.$this->search.'%')->where('user_id',Auth::user()->id)
                        ->where('order_number','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('vehicle', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('asset.brand', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('container', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy('order_number','desc')->paginate(10),
                        'fuel_filter' => $this->fuel_filter,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
                }elseif(isset($this->container_id)){
                    return view('livewire.fuels.manage',[
                        'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                        ])->where('container_id',$this->container_id)->where('user_id',Auth::user()->id)->whereMonth($this->fuel_filter, date('m'))
                        ->whereYear($this->fuel_filter, date('Y'))->orderBy('order_number','desc')->paginate(10),
                        'fuel_filter' => $this->fuel_filter,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
                }
                else {
                    
                    return view('livewire.fuels.manage',[
                        'fuels' => Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
                        ])->whereMonth($this->fuel_filter, date('m'))
                        ->whereYear($this->fuel_filter, date('Y'))->where('user_id',Auth::user()->id)->orderBy('order_number','desc')->paginate(10),
                        'fuel_filter' => $this->fuel_filter,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);

                }
    
            }
    
    }
}
