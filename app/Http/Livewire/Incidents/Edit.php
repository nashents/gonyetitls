<?php

namespace App\Http\Livewire\Incidents;

use App\Models\Loss;
use App\Models\Trip;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Contact;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Incident;
use App\Models\BasicCause;
use App\Models\Destination;
use App\Models\Measurement;
use App\Models\Transporter;
use App\Models\IncidentDate;
use App\Models\IncidentOther;
use Livewire\WithFileUploads;
use App\Models\ImmediateCause;
use App\Models\IncidentDamage;
use App\Models\IncidentInjury;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    use WithFileUploads;

 
    public $type = NULL;
    public $assigned_to = NULL;
    public $horses;
    public $horse;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $transporters;
    public $selectedTransporter;
    public $transporter;
    public $customers;
    public $customer_id;
    public $employees;
    public $employee_id;
    public $drivers;
    public $losses;
    public $driver_id;
    public $destinations;
    public $destination_id;
    public $cargos;
    public $cargo;
    public $selectedCargo;
    public $cargo_type;
    public $measurements;
    public $measurement_id;
    public $currencies;
    public $currency_id;
    public $incident_type;
    public $category ;
    public $trips;
    public $trip;
    public $selectedTrip;
    public $create_trip;

  


    public $searchHorse;
    public $searchVehicle;
    public $vehicle;
    public $searchEmployee;
    public $searchDriver;
    public $searchCustomer;
    public $searchTrip;
    
    protected $queryString = ['searchTrip','searchVehicle','searchCustomer','searchHorse','searchEmployee','searchDriver'];

   
    public $date;
    public $report_date;
    public $weight;
    public $quantity;
    public $litreage;
    public $litreage_at_20;
    public $incident_number;
    public $location;
    public $loss_potential;
    public $occurance;
    public $occupation;
    public $experience;
    public $controling_activity;
    public $media_coverage;
    public $description;
    public $immediate_cause_id;
    public $basic_cause_id;
    public $corrections;
    public $authorities;
    public $report;
    public $status;

    // injury
    public $name;
    public $taken_to;
    public $injury_object;
    public $body_part;
    public $days_lost;
    public $nature_of_injury;

    //property damage
    public $damage;
    public $damage_object;
    public $nature_of_damage;
    public $damage_currency_id;
    public $estimated_cost;
    public $actual_cost;

    //other incident
    public $other_type;
    public $nature_of_loss;
    public $cost;
    public $other_currency_id;
    public $other_object;

    public $basic_causes;
    public $immediate_causes;
    public $incident_injuries;
    public $incident_damages;
    public $incident_others;
    public $contacts;
    public $incident_dates;
   
    // contacts

    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $department;

    //followup dates

    public $followup_date;


    public $inputs = [];
    public $i = 1;
    public $n = 1;
    
    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    
    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public $basic_causes_inputs = [];
    public $z = 1;
    public $x = 1;
    
    public function basicAdd($z)
    {
        $z = $z + 1;
        $this->z = $z;
        array_push($this->basic_causes_inputs ,$z);
    }
    
    public function basicRemove($z)
    {
        unset($this->basic_causes_inputs[$z]);
    }
    public $immediate_causes_inputs = [];
    public $r = 1;
    public $s = 1;
    
    public function immediateAdd($r)
    {
        $r = $r + 1;
        $this->r = $r;
        array_push($this->immediate_causes_inputs ,$r);
    }
    
    public function immediateRemove($r)
    {
        unset($this->immediate_causes_inputs[$r]);
    }
    
    public $contacts_inputs = [];
    public $o = 1;
    public $m = 1;

    public function contactsAdd($o)
    {
        $o = $o + 1;
        $this->o = $o;
        array_push($this->contacts_inputs ,$o);
    }

    public function contactsRemove($o)
    {
        unset($this->contacts_inputs[$o]);
    }

    public $damages_inputs = [];
    public $d = 1;
    public $e = 1;

    public function damagesAdd($d)
    {
        $d = $d + 1;
        $this->d = $d;
        array_push($this->damages_inputs ,$d);
    }

    public function damagesRemove($d)
    {
        unset($this->damages_inputs[$d]);
    }

    public $injuries_inputs = [];
    public $k = 1;
    public $j = 1;

    public function injuriesAdd($k)
    {
        $k = $k + 1;
        $this->k = $k;
        array_push($this->injuries_inputs ,$k);
    }

    public function injuriesRemove($k)
    {
        unset($this->injuries_inputs[$k]);
    }

    public $others_inputs = [];
    public $p = 1;
    public $q = 1;

    public function othersAdd($p)
    {
        $p = $p + 1;
        $this->p = $p;
        array_push($this->others_inputs ,$p);
    }

    public function othersRemove($p)
    {
        unset($this->others_inputs[$p]);
    }

    public $document_inputs = [];
    public $a = 1;
    public $b = 1;

    public function documentsAdd($a)
    {
        $a = $a + 1;
        $this->a = $a;
        array_push($this->documents_inputs ,$a);
    }

    public function documentsRemove($a)
    {
        unset($this->documents_inputs[$a]);
    }

    public $dates_inputs = [];
    public $g = 1;
    public $h = 1;

    public function datesAdd($g)
    {
        $g = $g + 1;
        $this->g = $g;
        array_push($this->dates_inputs ,$g);
    }

    public function datesRemove($g)
    {
        unset($this->dates_inputs[$g]);
    }


    public function mount($id){
        $incident = Incident::find($id);
        $this->incident_number = $incident->incident_number;
        $this->description = $incident->description;
        $this->date = $incident->date;
        $this->report_date = $incident->report_date;
        $this->report = $incident->report;
        $this->location = $incident->location;
        $this->loss_potential = $incident->loss_potential;
        $this->occurance = $incident->occurance;
        $this->occupation = $incident->occupation;
        $this->experience = $incident->experience;
        $this->controling_activity = $incident->controling_activity;
        $this->media_coverage = $incident->media_coverage;
        $this->incident_type = $incident->incident_type;
        $this->category = $incident->category;
        $this->create_trip = $incident->create_trip;
        $this->selectedHorse = $incident->horse_id;
        $this->selectedVehicle = $incident->vehicle_id;
        $this->employee_id = $incident->employee_id;
        $this->driver_id = $incident->driver_id;
        $this->corrections = $incident->corrections;
        $this->customer_id = $incident->customer_id;
        $this->selectedTransporter = $incident->transporter_id;
        $this->selectedCargo = $incident->cargo_id;
        $this->cargo_type = $incident->cargo ? $incident->cargo->type : Null;
        $this->weight = $incident->weight;
        $this->authorities = $incident->authorities;
        $this->quantity = $incident->quantity;
        $this->litreage = $incident->litreage;
        $this->litreage_at_20 = $incident->litreage_at_20;
        $this->measurement_id = $incident->measurement_id;
        $this->destination_id = $incident->destination_id;
        $this->trip = $incident->trip;
        $this->selectedTrip = $incident->trip_id;
        $this->type = $incident->type;
        $this->assigned_to = $incident->assigned_to;

        $this->basic_causes = $incident->basic_causes;
        if (isset($this->basic_causes)) {
            foreach ($this->basic_causes as $key => $cause) {
                $this->basic_cause_id[$key] = $cause->loss_id;
            }
        }
        
        $this->immediate_causes = $incident->immediate_causes;
        if (isset($this->immediate_causes)) {
            foreach ($this->immediate_causes as $key => $cause) {
                $this->immediate_cause_id[$key] = $cause->loss_id;
            }
        }

        $this->incident_injuries = $incident->incident_injuries;
        if (isset($this->incident_injuries)) {
            foreach ($this->incident_injuries as $key => $injury) {
                $this->name[$key] = $injury->name;
                $this->taken_to[$key] = $injury->taken_to;
                $this->body_part[$key] = $injury->body_part;
                $this->days_lost[$key] = $injury->days_lost;
                $this->nature_of_injury[$key] = $injury->nature_of_injury;
                $this->injury_object[$key] = $injury->object;
            }
        }

        $this->incident_damages = $incident->incident_damages;
        if (isset($this->incident_damages)) {
            foreach ($this->incident_damages as $key => $damage) {
                $this->damage[$key] = $damage->damage;
                $this->nature_of_damage[$key] = $damage->nature_of_damage;
                $this->estimated_cost[$key] = $damage->estimated_cost;
                $this->actual_cost[$key] = $damage->actual_cost;
                $this->damage_currency_id[$key] = $damage->currency_id;
                $this->damage_object[$key] = $damage->object;
            }
        }

        $this->contacts = $incident->contacts;
        if (isset($this->contacts)) {
            foreach ($this->contacts as $key => $contact) {
                $this->contact_name[$key] = $contact->name;
                $this->contact_surname[$key] = $contact->surname;
                $this->contact_email[$key] = $contact->email;
                $this->contact_phonenumber[$key] = $contact->phonenumber;
                $this->department[$key] = $contact->department;
            }
        }

        $this->incident_others = $incident->incident_others;
        if (isset($this->incident_others)) {
            foreach ($this->incident_others as $key => $other) {
                $this->other_type[$key] = $other->type;
                $this->nature_of_loss[$key] = $other->nature_of_loss;
                $this->cost[$key] = $other->cost;
                $this->other_currency_id[$key] = $other->currency_id;
                $this->other_object[$key] = $other->object_other;
            }
        }


        $this->incident_dates = $incident->incident_dates;
        if (isset($this->incident_dates)) {
            foreach ($this->incident_dates as $key => $date) {
                $this->followup_date[$key] = $date->date;
            }
        }

         if (isset($this->selectedTransporter)) {
            $this->horses = Horse::where('transporter_id', $this->selectedTransporter)->where('service', 0)->get();
            $this->vehicles = Vehicle::where('transporter_id', $this->selectedTransporter)->where('service', 0)->get(); 
            $this->drivers = Driver::withAggregate('employee','name')->where('transporter_id',$this->selectedTransporter)
                    ->orderBy('employee_name','asc')->latest()->get();
        } else {
            $this->vehicles = collect();
            $this->drivers = collect();
            $this->horses = collect();
        }

        $this->employees = Employee::orderBy('surname','asc')->get()->sortBy('name');
        $this->drivers = collect();

        $this->vehicles = collect();

        $this->losses = Loss::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->destinations = Destination::latest()->get();
       
    }


     

    public function updatedCategory($value){

        if ($value == "Trip") {

            $tripQuery = Trip::query()
                    ->select('id', 'trip_number', 'trip_ref','start_date', 'customer_id', 'driver_id', 'horse_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'driver',
                        'horse:id,registration_number,fleet_number',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->whereYear('start_date', date('Y'))
                    ->where('authorization', 'approved')
                    ->where('trip_status', '!=', 'Cancelled');

            if (filled($this->searchTrip)) {
                $term = '%'.$this->searchTrip.'%';

                $tripQuery->where(function ($q) use ($term) {
                    $q->where('trip_number', 'like', $term)
                    ->orWhere('trip_ref', 'like', $term)
                    ->orWhereHas('horse', function ($qq) use ($term) {
                        $qq->where('registration_number', 'like', $term);
                    });
                });
            }

            $this->trips = $tripQuery
                ->orderBy('id', 'desc')
                ->take(50)
                ->get();
        }
    }

    public function updatedSearchTrip($value){

         $tripQuery = Trip::query()
                    ->select('id', 'trip_number', 'trip_ref','start_date', 'customer_id', 'driver_id', 'horse_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'driver',
                        'horse:id,registration_number,fleet_number',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->whereYear('start_date', date('Y'))
                    ->where('authorization', 'approved')
                    ->where('trip_status', '!=', 'Cancelled');

            if (filled($value)) {
                $term = '%'.$value.'%';

                $tripQuery->where(function ($q) use ($term) {
                    $q->where('trip_number', 'like', $term)
                    ->orWhere('trip_ref', 'like', $term)
                    ->orWhereHas('horse', function ($qq) use ($term) {
                        $qq->where('registration_number', 'like', $term);
                    });
                });
            }

            $this->trips = $tripQuery
                ->orderBy('id', 'desc')
                ->take(50)
                ->get();
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[
      'selectedVehicle.required' => 'Select Vehicle',
      'employee_id.required' => 'Select Employee',
      'driver_id.required' => 'Select Driver',
      'selectedHorse.required' => 'Select Horse',

  ];
    protected $rules = [
        'incident_number' => 'required',
        'description' => 'required',
       

    ];


    public function updatedSelectedCargo($id)
    {
            if (!is_null($id)) {
                $this->cargo = Cargo::find($id);
                $this->cargo_type = $this->cargo->type;
            }
    }

    public function updatedSelectedTransporter($id)
    {
            if (!is_null($id)) {
                $this->transporter = Transporter::find($id);
                if (isset($this->transporter)) {
                    $this->horses = $this->transporter->horses->where('service', 0);
                    $this->vehicles = $this->transporter->vehicles->where('service', 0);
                    $this->drivers = Driver::withAggregate('employee','name')->where('transporter_id',$id)
                    ->orderBy('employee_name','asc')->latest()->get();
                }
                
            }
    }

    public function updatedSelectedHorse($id)
    {
            if (!is_null($id)) {
                $this->horse = Horse::find($id);
                if (isset($this->horse)) {
                    $this->trips = $this->horse->trips;
                }
               
            }
    }
    public function updatedSelectedVehicle($id)
    {
            if (!is_null($id)) {
                $this->vehicle = Vehicle::find($id);
                if (isset($this->vehicle)) {
                    $this->trips = $this->vehicle->trips;
                }
               
            }
    }


    public function updatedSelectedTrip($id)
    {
            if (!is_null($id)) {
                $this->trip = Trip::find($id);
                if ($this->trip) {
                    $this->destination_id = $this->trip->to;
                    $this->selectedTransporter = $this->trip->transporter_id;
                    $this->assigned_to = "Driver";
                    $this->driver_id = $this->trip->driver_id;
                    if($this->trip->horse_id){
                        $this->selectedHorse = $this->trip->horse_id;
                        $this->type = "Horse";
                    }
                    if($this->trip->vehicle_id){
                        $this->selectedVehicle = $this->trip->vehicle_id;
                    }
                    $this->cargo = $this->trip->cargo;
                    $this->selectedCargo = $this->trip->cargo_id;
                    if (isset($this->cargo)) {
                        $this->cargo_type = $this->cargo->type;
                    } 
                    $this->weight = $this->trip->weight;
                    $this->quantity = $this->trip->quantity;
                    $this->litreage = $this->trip->litreage;
                    $this->litreage_at_20 = $this->trip->litreage_at_20;
                    $measurement = Measurement::where('name', $this->trip->measurement)->first();
                    if ($measurement) {
                        $this->measurement_id = $measurement->id;
                    }
                   
                    $this->destination_id = $this->trip->to;
                }
               
            }
    }

    public function update(){
        
        $incident = Incident::find($this->incident_id);
        $incident->incident_number = $this->incidentNumber();
        $incident->user_id = Auth::user()->id;
        $incident->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
        $incident->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
        $incident->employee_id = $this->employee_id ? $this->employee_id : Null;
        $incident->driver_id = $this->driver_id ? $this->driver_id : Null;
        $incident->customer_id = $this->customer_id ? $this->customer_id : Null;
        $incident->trip_id = $this->selectedTrip;
        $incident->destination_id = $this->destination_id;
        $incident->transporter_id = $this->selectedTransporter;
        $incident->cargo_id = $this->selectedCargo;
        $incident->incident_type = $this->incident_type;
        $incident->category = $this->category;
        $incident->create_trip = $this->create_trip;
        $incident->weight = $this->weight;
        $incident->quantity = $this->quantity;
        $incident->litreage = $this->litreage;
        $incident->litreage_at_20 = $this->litreage_at_20;
        $incident->measurement_id = $this->measurement_id;
        $incident->location = $this->location;
        $incident->date = $this->date;
        $incident->type = $this->type;
        $incident->assigned_to = $this->assigned_to;
        $incident->report_date = $this->report_date;
        $incident->loss_potential = $this->loss_potential;
        $incident->occurance = $this->occurance;
        $incident->occupation = $this->occupation;
        $incident->experience = $this->experience;
        $incident->controling_activity = $this->controling_activity;
        $incident->media_coverage = $this->media_coverage;
        $incident->description = $this->description;
        $incident->corrections = $this->corrections;
        $incident->authorities = $this->authorities;
        $incident->report = $this->report;
        $incident->status = 1;
        $incident->update();

        if (isset($this->contacts)) {
            foreach ($this->contacts as $key => $id) {
               $contact = Contact::find($id);
               $contact->incident_id = $incident->id;
               $contact->category = 'incident';
               if (isset($this->contact_name[$key])) {
                $contact->name = $this->contact_name[$key];
               }
               if (isset($this->contact_surname[$key])) {
                $contact->surname = $this->contact_surname[$key];
               }
                if (isset($this->contact_phonenumber[$key])) {
                    $contact->phonenumber = $this->contact_phonenumber[$key];
                }
                if (isset($this->contact_email[$key])) {
                    $contact->email = $this->contact_email[$key];
                }
                if (isset($this->department[$key])) {
                    $contact->department = $this->department[$key];
                }
              
               $contact->update();
            }
        }

        if (isset($this->incident_injuries)) {
            foreach ($this->incident_injuries as $key => $id) {
               $injury = IncidentInjury::find($id);
               $injury->incident_id = $incident->id;
               if (isset($this->name[$key])) {
                $injury->name = $this->name[$key];
               }
               if (isset($this->taken_to[$key])) {
                $injury->taken_to = $this->taken_to[$key];
               }
                if (isset($this->body_part[$key])) {
                    $injury->body_part = $this->body_part[$key];
                }
                if (isset($this->days_lost[$key])) {
                    $injury->days_lost = $this->days_lost[$key];
                }
                if (isset($this->nature_of_injury[$key])) {
                    $injury->nature_of_injury = $this->nature_of_injury[$key];
                }
                if (isset($this->injury_object[$key])) {
                    $injury->object = $this->injury_object[$key];
                }
               $injury->update();
            }
        }
   
        if (isset($this->incident_damages)) {
            foreach ($this->incident_damages as $key => $id) {
               $damage = IncidentDamage::find($id);
               if (isset($this->damage[$key])) {
                $damage->damage = $this->damage[$key];
               }
               if (isset($this->nature_of_damage[$key])) {
                $damage->nature_of_damage = $this->nature_of_damage[$key];
               }
                if (isset($this->estimated_cost[$key])) {
                    $damage->estimated_cost = $this->estimated_cost[$key];
                }
                if (isset($this->actual_cost[$key])) {
                    $damage->actual_cost = $this->actual_cost[$key];
                }
                if (isset($this->damage_currency_id[$key])) {
                    $damage->currency_id = $this->damage_currency_id[$key];
                }
                if (isset($this->damage_object[$key])) {
                    $damage->object = $this->damage_object[$key];
                }
               $damage->update();
            }
        }

        if (isset($this->incident_others)) {
            foreach ($this->incident_others as $key => $id) {
               $other = IncidentOther::find($id);
               if (isset($this->other_type[$key])) {
                $other->type = $this->other_type[$key];
               }
                if (isset($this->cost[$key])) {
                    $other->cost = $this->cost[$key];
                }
                if (isset($this->nature_of_loss[$key])) {
                    $other->nature_of_loss = $this->nature_of_loss[$key];
                }
                if (isset($this->other_currency_id[$key])) {
                    $other->currency_id = $this->other_currency_id[$key];
                }
                if (isset($this->other_object[$key])) {
                    $other->object_other = $this->other_object[$key];
                }
               
               $other->update();
            }
        }

        if (isset($this->incident_dates)) {
            foreach ($this->incident_dates as $key => $id) {
               $date = IncidentDate::find($id);
               if (isset($this->followup_date[$key])) {
                $date->date = $this->followup_date[$key];
               }
               $date->update();
            }
        }
   
        if (isset($this->immediate_causes)) {
            foreach ($this->immediate_causes as $key => $id) {
               $immediate_cause = ImmediateCause::find($id);
               if (isset($this->immediate_cause_id[$key])) {
                $immediate_cause->loss_id = $this->immediate_cause_id[$key];
               }
               $immediate_cause->update();
            }
        }
        if (isset($this->basic_causes)) {
            foreach ($this->basic_causes as $key => $id) {
               $basic_cause = BasicCause::find($id);
               if (isset($this->basic_cause_id[$key])) {
                $basic_cause->loss_id = $this->basic_cause_id[$key];
               }
               $basic_cause->update();
            }
        }

        Session::flash('success','Incident Updated Successfully!!');
        return redirect()->route('incidents.index');
    }


    public function render()
    {

        if (isset($this->searchHorse)) {
            $this->horses = Horse::query()->with('horse_make:id,name','horse_model:id,name')->where('registration_number', 'like', '%'.$this->searchHorse.'%')->get();
        }
        if (isset($this->searchcustomer)) {
            $this->customers = Customer::where('name', 'LIKE', "%".$this->searchCustomer."%")->get();
        }
        if (isset($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();
            
        }
       
        if (isset($this->searchEmployee)) {
            $this->employees = Employee::where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchEmployee."%")
            ->get();
        }
       
        return view('livewire.incidents.edit');
    }
}
