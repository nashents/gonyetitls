<?php

namespace App\Http\Livewire\TyreAssignments;

use App\Models\Tyre;
use App\Models\Horse;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Movement;
use App\Models\TyreDispatch;
use Livewire\WithPagination;
use App\Models\TyreAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';


    public $search;
    public $searchTyres;
    public $statusFilter = '';
    protected $queryString = ['search', 'searchTyres', 'statusFilter' => ['except' => '']];

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
    public $user_id;
    public $unassigned_date;
    public $unassignment_reason;
    public $unassign_tyre_label;
    public $unassign_starting_odometer;

    public function mount(){
        $this->resetPage();
        $this->vehicles = Vehicle::where('archive', 0)->where('status',1)->orderByIdentifier('asc')->get();
        $this->trailers = Trailer::where('archive', 0)->where('status', 1)->orderByIdentifier('asc')->get();
        $this->horses = Horse::where('archive', 0)->where('status',1)->orderByIdentifier('asc')->get();
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

        if ($this->tyreAlreadyAssigned($this->tyre_id)) {
            return;
        }

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

                if ($this->tyreAlreadyAssigned($this->tyre_id, $this->tyre_assignment_id)) {
                    return;
                }

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

        private function tyreAlreadyAssigned($tyreId, $exceptId = null){
            if (!$tyreId) {
                return false;
            }

            $active = TyreAssignment::activeForTyre($tyreId, $exceptId);
            if (!$active) {
                return false;
            }

            $message = TyreAssignment::alreadyAssignedMessage($active);
            $this->addError('tyre_id', $message);
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>$message
            ]);

            return true;
        }

        public function unAssignment($id){
            $assignment = TyreAssignment::with('tyre.product','horse','trailer','vehicle')->find($id);
            if (!$assignment || $assignment->status != 1) {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Active Tyre Assignment Not Found!!"
                ]);
                return;
            }

            $this->resetErrorBag();
            $this->tyre_assignment_id = $assignment->id;
            $this->unassign_starting_odometer = $assignment->starting_odometer;
            $this->unassign_tyre_label = trim(
                optional(optional($assignment->tyre)->product)->name.' '.
                (optional($assignment->tyre)->serial_number ? 'SN#: '.$assignment->tyre->serial_number : '')
            );

            $asset = $assignment->horse ?? $assignment->trailer ?? $assignment->vehicle;
            $this->ending_odometer = optional($asset)->mileage;
            $this->unassigned_date = date('Y-m-d');
            $this->unassignment_reason = '';
            $this->dispatchBrowserEvent('show-unAssignmentModal');
        }

        public function updateAssignment(){
            $assignment = TyreAssignment::find($this->tyre_assignment_id);
            if (!$assignment || $assignment->status != 1) {
                $this->dispatchBrowserEvent('hide-unAssignmentModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Active Tyre Assignment Not Found!!"
                ]);
                return;
            }

            $odometerRule = ['required', 'numeric', 'min:0'];
            if (is_numeric($assignment->starting_odometer)) {
                $odometerRule[] = 'gte:unassign_starting_odometer';
            }

            $this->validate([
                'ending_odometer' => $odometerRule,
                'unassigned_date' => 'required|date|before_or_equal:today',
                'unassignment_reason' => 'required|string|max:1000',
            ], [
                'ending_odometer.gte' => 'Unassignment mileage cannot be less than the fitting mileage ('.$assignment->starting_odometer.').',
            ]);

            $assignment->ending_odometer = $this->ending_odometer;
            $assignment->unassigned_date = $this->unassigned_date;
            $assignment->unassignment_reason = $this->unassignment_reason;
            $assignment->unassigned_by = Auth::user()->id;
            $assignment->status = 0;
            $assignment->update();

            // Add the distance covered on this fitting to the tyre's running total.
            $distance = is_numeric($assignment->starting_odometer)
                ? max(0, (float) $this->ending_odometer - (float) $assignment->starting_odometer)
                : 0;

            $tyre = Tyre::find($assignment->tyre_id);
            if ($tyre) {
                $tyre->mileage = (float) $tyre->mileage + $distance;
                $tyre->status = 1;
                $tyre->update();
            }

            $this->dispatchBrowserEvent('hide-unAssignmentModal');
            Session::flash('success','Tyre Unassigned Successfully!! '.number_format($distance)."Kms added to the tyre's distance.");
            return redirect(request()->header('Referer'));
        }

        public function updatingSearch()
        {
            $this->resetPage();
        }

        public function updatingStatusFilter()
        {
            $this->resetPage();
        }
    public function render()
    {

        // Tyres already on an asset are hidden; when editing, keep the assignment's own tyre listed.
        $availableTyres = function ($q) {
            $q->whereDoesntHave('tyre_assignments', fn ($a) => $a->where('status', 1))
              ->when($this->tyre_assignment_id && $this->tyre_id, fn ($q) => $q->orWhere('id', $this->tyre_id));
        };

         if (filled($this->searchTyres)) {
            $term = $this->searchTyres;
            $this->tyres = Tyre::query()
                ->with([
                    'product:id,name,identification_number,brand_id',
                    'product.brand:id,name',   // if you need the brand in the view
                ])
                ->where('disposed', 0)
                ->where('retread', 0)
                ->where($availableTyres)
                ->when($term !== '', function ($q) use ($term) {
                    $like = "%{$term}%";
                    $q->where(function ($q) use ($like) {
                        $q->where('serial_number', 'like', $like)
                        ->orWhereHas('product', function ($q) use ($like) {
                            $q->where('name', 'like', $like)
                                ->orWhere('identification_number', 'like', $like)
                                ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $like));
                        });
                    });
                })
                ->get();
        }else{
             $this->tyres = Tyre::query()
                    ->where('disposed', 0)
                    ->where('retread', 0)
                    ->where($availableTyres)
                    ->get();
        }

        $term = $this->search;

        $tyre_assignments = TyreAssignment::query()
            ->with('horse','vehicle','trailer','tyre','tyre.product','tyre.product.brand','unassignedBy')
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('status', 1))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where(fn ($q) => $q->where('status', '!=', 1)->orWhereNull('status')))
            ->when(filled($term), function ($q) use ($term) {
                $like = '%'.$term.'%';
                $q->where(function ($q) use ($like) {
                    $q->whereHas('tyre', fn ($t) => $t->where('tyre_number', 'like', $like)->orWhere('serial_number', 'like', $like))
                      ->orWhereHas('tyre.product', fn ($p) => $p->where('name', 'like', $like))
                      ->orWhereHas('tyre.product.brand', fn ($b) => $b->where('name', 'like', $like))
                      ->orWhereHas('horse', fn ($h) => $h->where('registration_number', 'like', $like))
                      ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', $like))
                      ->orWhereHas('trailer', fn ($t) => $t->where('registration_number', 'like', $like));
                });
            })
            ->orderBy('created_at','desc')
            ->paginate(10);

        return view('livewire.tyre-assignments.index',[
            'tyre_assignments' => $tyre_assignments,
        ]);
    }
}
