<?php

namespace App\Http\Livewire\TyreAssignments;

use App\Models\Tyre;
use App\Models\Horse;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Movement;
use App\Models\TyreDetail;
use App\Models\TyreDispatch;
use Livewire\WithPagination;
use App\Models\TyreAssignment;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';


    public $search;
    public $searchTyres;
    protected $queryString = ['search', 'searchTyres'];

    private $tyre_assignments;
    public $tyre_assignment_id;
    public $tyres;
    public $type = "Horse";
    public $tyre_id;
    public $horses;
    public $horse_id;
    public $vehicles;
    public $vehicle_id;
    public $trailers;
    public $trailer_id;
    public $position;
    public $axle;
    public $starting_odometer;
    public $date_fitted;
    public $current_mileage;
    public $ending_odometer;
    public $description;
    public $status;

    public function mount(){
        $this->resetPage();
        $this->vehicles = Vehicle::where('status',1)->orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::where('status', 1)->orderBy('registration_number','asc')->get();
        $this->horses = Horse::where('status',1)->orderBy('registration_number','asc')->get();
    }

    private function resetInputFields(){
        $this->vehicle_id = '';
        $this->tyre_id = '';
        $this->horse_id = '';
        $this->trailer_id = '';
        $this->position = '';
        $this->starting_odometer = '';
        $this->description = '';
        $this->position = '';
        $this->axle = '';
        $this->type = '';
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'trailer_id' => 'required',
        'vehicle_id' => 'required',
        'horse_id' => 'required',
        'tyre_id' => 'required',
        'starting_odometer' => 'required',
        'position' => 'required',
        'axle' => 'required',
        'description' => 'nullable|string',
    ];

    public function store(){

        $assignment = new TyreAssignment;
        $assignment->user_id = Auth::user()->id;
        $assignment->tyre_id = $this->tyre_id;
        $assignment->type = $this->type;
        if ($this->type == "Horse") {
            $assignment->horse_id = $this->horse_id;
            $assignment->vehicle_id = null;
            $assignment->trailer_id = null;
        }elseif ($this->type == "Trailer") {
            $assignment->trailer_id = $this->trailer_id;
            $assignment->horse_id = null;
            $assignment->vehicle_id = null;
        }elseif ($this->type == "Vehicle") {
            $assignment->vehicle_id = $this->vehicle_id;
            $assignment->horse_id = null;
            $assignment->trailer_id = null;
        }
        $assignment->starting_odometer = $this->starting_odometer;
        $assignment->position = $this->position;
        $assignment->axle = $this->axle;
        $assignment->description = $this->description;
        $assignment->date_fitted = $this->date_fitted;
        $assignment->current_mileage = $this->current_mileage;
        $assignment->status = 1;
        $assignment->save();

        $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
        $movement->user_id = $assignment->user_id;
        $movement->tyre_id = $assignment->tyre_id;
        
        if ($assignment->horse_id) {
            $movement->location = 'Horse';
            $movement->horse_id = $assignment->horse_id;
        } elseif ($assignment->vehicle_id) {
            $movement->location = 'Vehicle';
            $movement->vehicle_id = $assignment->vehicle_id;
        } elseif ($assignment->trailer_id) {
            $movement->location = 'Trailer';
            $movement->trailer_id = $assignment->vehicle_id;
        }
        
        $movement->current_mileage = $assignment->current_mileage;
        $movement->mileage_moved = $assignment->starting_odometer;
        $movement->date =   $assignment->date_fitted;
        $movement->save();

        $mileage = new Mileage;
        $mileage->user_id = Auth::user()->id;
        $mileage->tyre_assignment_id = $assignment->id;
        $mileage->horse_id = $this->horse_id ? $this->horse_id : Null;
        $mileage->vehicle_id = $this->vehicle_id ? $this->vehicle_id : Null;
        $mileage->trailer_id = $this->trailer_id ? $this->trailer_id : Null;
        $mileage->mileage = $this->starting_odometer;
        $mileage->date = date('Y-m-d');
        $mileage->category = "Tyre Assignment";
        $mileage->save();

        $tyre = Tyre::find($this->tyre_id);
        $dispatch = new TyreDispatch;
        $dispatch->tyre_assignment_id = $assignment->id;
        $dispatch->tyre_id = $this->tyre_id;
        $dispatch->tyre_number = $tyre->tyre_number;
        $dispatch->serial_number = $tyre->serial_number;
        $dispatch->width = $tyre->width;
        $dispatch->aspect_ratio = $tyre->aspect_ratio;
        $dispatch->diameter =  $tyre->diameter;
        $dispatch->horse_id = $this->horse_id;
        $dispatch->vehicle_id = $this->vehicle_id;
        $dispatch->trailer_id = $this->trailer_id;
        $dispatch->save();

        $tyre = Tyre::find($this->tyre_id);
        $tyre->status = 0;
        $tyre->update();

        $this->dispatchBrowserEvent('hide-tyre_assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Tyre Assignment Saved Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

    }

    public function edit($id){

        $assignment = TyreAssignment::find($id);
        $this->user_id = $assignment->user_id;
        $this->horse_id = $assignment->horse_id;
        $this->vehicle_id = $assignment->vehicle_id;
        $this->trailer_id = $assignment->trailer_id;
        $this->type = $assignment->type;
        $this->tyre_id = $assignment->tyre_id;
        $this->starting_odometer = $assignment->starting_odometer;
        $this->ending_odometer = $assignment->ending_odometer;
        $this->position = $assignment->position;
        $this->axle = $assignment->axle;
        $this->description = $assignment->description;
        $this->status = $assignment->status;
        $this->tyre_assignment_id = $assignment->id;
        $this->dispatchBrowserEvent('show-tyre_assignmentEditModal');

        }


        public function update()
        {
            if ($this->tyre_assignment_id) {

                $assignment = TyreAssignment::find($this->tyre_assignment_id);
                $assignment->user_id = Auth::user()->id;

                if ($this->type == "Horse") {
                    $assignment->horse_id = $this->horse_id;
                    $assignment->vehicle_id = null;
                    $assignment->trailer_id = null;
                }elseif ($this->type == "Trailer") {
                    $assignment->trailer_id = $this->trailer_id;
                    $assignment->horse_id = null;
                    $assignment->vehicle_id = null;
                }elseif ($this->type == "Vehicle") {
                    $assignment->vehicle_id = $this->vehicle_id;
                    $assignment->horse_id = null;
                    $assignment->trailer_id = null;
                }
                $assignment->tyre_id = $this->tyre_id;
                $assignment->type = $this->type;
                $assignment->starting_odometer = $this->starting_odometer;
                $assignment->ending_odometer = $this->ending_odometer;
                $assignment->position = $this->position;
                $assignment->axle = $this->axle;
                $assignment->description = $this->description;
                if ($this->ending_odometer) {
                    $assignment->status = 0;
                    $tyre = Tyre::find($this->tyre_id);
                    $tyre->status = 1;
                    $tyre->update();
                }
               
                $assignment->update();

                $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
                $movement->user_id = $assignment->user_id;
                $movement->tyre_id = $assignment->tyre_id;
                
                if ($assignment->horse_id) {
                    $movement->location = 'Horse';
                    $movement->horse_id = $assignment->horse_id;
                } elseif ($assignment->vehicle_id) {
                    $movement->location = 'Vehicle';
                    $movement->vehicle_id = $assignment->vehicle_id;
                } elseif ($assignment->trailer_id) {
                    $movement->location = 'Trailer';
                    $movement->trailer_id = $assignment->vehicle_id;
                }
                
                $movement->current_mileage = $assignment->current_mileage;
                $movement->mileage_moved = $assignment->starting_odometer;
                $movement->date =   $assignment->date_fitted;
                $movement->save();

                $mileage = Mileage::where('tyre_assignment_id',$assignment->id)->first();
                if (isset($mileage)) {
                    $mileage->tyre_assignment_id = $assignment->id;
                    $mileage->horse_id = $this->horse_id ? $this->horse_id : Null;
                    $mileage->vehicle_id = $this->vehicle_id ? $this->vehicle_id : Null;
                    $mileage->trailer_id = $this->trailer_id ? $this->trailer_id : Null;
                    $mileage->mileage = $this->starting_odometer;
                    $mileage->date = date('Y-m-d');
                    $mileage->category = "Tyre Assignment";
                    $mileage->update();
                }
              

                $this->dispatchBrowserEvent('hide-tyre_assignmentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Tyre Assignment Updated Successfully!!"
                ]);

                return redirect(request()->header('Referer'));

            }else {
                $this->dispatchBrowserEvent('hide-tyre_assignmentEditModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Tyre Assignment Not Found!!"
                ]);
            }
        }

        public function unAssignment($id){
            $assignment = Assignment::find($id);
            $this->assignment_id = $assignment->id;
            $this->dispatchBrowserEvent('show-unAssignmentModal');
        }

        public function updateAssignment(){
           $assignment = Assignment::find($this->assignment_id);
           $assignment->ending_odometer = $this->ending_odometer;
           $assignment->end_date = $this->end_date;
           $assignment->status = 0;
           $assignment->update();
           Session::flash('success','Driver horse Unassignment Successful');
            $this->dispatchBrowserEvent('hide-unAssignmentModal');
            return redirect()->route('assignments.index');
        }

        public function updatingSearch()
        {
            $this->resetPage();
        }
    public function render()
    {

         if (filled($this->searchTyres)) {
            $this->tyres = Tyre::query()->where('status',1)->whereDoesntHave('tyre_assignments', function ($query) {
                            $query->where('status', 1);
                        })->where('serial_number', 'like', '%'.$this->searchTyres.'%')
                         ->whereHas('product', function ($query) {
                            return $query->where('name', 'like', '%'.$this->searchTyres.'%');
                        })
                        ->get();
        }else{
             $this->tyres = Tyre::where('status',1)->whereDoesntHave('tyre_assignments', function ($query) {
                            $query->where('status', 1);
                        })->get();
        }

        if (isset($this->search)) {
            return view('livewire.tyre-assignments.index',[
                'tyre_assignments' => TyreAssignment::query()->with('horse','vehicle','trailer','tyre','tyre.product','tyre.product.brand')
                ->where('status',1)
                ->whereHas('tyre', function ($query) {
                    return $query->where('tyre_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre.product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre.product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre', function ($query) {
                    return $query->where('serial_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
               
                ->orderBy('created_at','desc')->paginate(10),
        
            ]);
        }
        else {
           
            return view('livewire.tyre-assignments.index',[
                'tyre_assignments' => TyreAssignment::query()->with('horse','vehicle','trailer','tyre')->where('status',1)->orderBy('created_at','desc')->paginate(10),
            ]);
          
        }
      
      
    }
}
