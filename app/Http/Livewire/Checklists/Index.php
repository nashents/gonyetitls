<?php

namespace App\Http\Livewire\Checklists;

use Carbon\Carbon;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Checklist;
use Livewire\WithPagination;
use App\Models\ChecklistItem;
use App\Models\ChecklistResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{


    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $checklist_items;
    public $checklist_item_id;
    private $checklists;
    public $checklist_id;
    public $trailers;
    public $trailer_id;
    public $vehicles;
    public $vehicle_id;
    public $drivers;
    public $driver_id;
    public $driver;
    public $employees;
    public $employee_id;
    public $horses;
    public $horse_id;
    public $description;
    public $date;
    public $checklist_filter = "date";
    public $available = '1';
    public $notavailable = '0';
    public $comments;
    public $status;
 

    public function mount(){
        $this->driver = Auth::user()->employee->driver;
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
    ];

    public function store(){
        try{

        $checklist = new Checklist;
        $checklist->user_id = Auth::user()->id;
        $checklist->checklist_number = $this->checklistNumber();
        $checklist->employee_id = $this->employee_id;
        $checklist->vehicle_id = $this->vehicle_id;
        $checklist->trailer_id = $this->trailer_id;
        $checklist->horse_id = $this->horse_id;
        $checklist->date = $this->date;
        $checklist->comments = $this->description;
        $checklist->save();

        
        if (isset($this->status)) {

            foreach ($this->status as $key => $value) {
            $result = new ChecklistResult;
            $result->checklist_id = $checklist->id;
            if (isset($this->status[$key])) {
                $result->status = $this->status[$key];
            }
            if (isset($this->comments[$key])) {
                $result->comments = $this->comments[$key];
            }
            $result->checklist_item_id = $key;
          
    
            $result->save();
    
              }
              
         
            }
            $this->dispatchBrowserEvent('hide-checklistModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Completed Successfully!!"
            ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating checklist!!"
        ]);
    }
    }


   
    public function render()
    {

         $base = Checklist::query()
                ->with([
                    'employee','checklist_category','horse','driver','vehicle','trailer',
                ])->when($this->driver?->id, function ($q) {
            $q->where('driver_id', $this->driver->id);
            });

            // Date filter: use provided range, else current month
            $base->when(filled($this->from) && filled($this->to), function ($q) {
                $q->whereDate($this->checklist_filter, '>=', $this->from)
                ->whereDate($this->checklist_filter, '<=', $this->to);
            }, function ($q) {
                $q->whereMonth($this->checklist_filter, Carbon::now()->month)
                ->whereYear($this->checklist_filter, Carbon::now()->year);
            });

            // Search filter (grouped to keep AND/OR logic correct)
            $base->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($qq) use ($term) {
                    $qq->where('checklist_number', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term);
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term);
                    })
                    ->orWhereHas('driver', function ($sub) use ($term) {
                        $sub->whereHas('employee', function ($emp) use ($term) {
                            $emp->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                        });
                    })
                    ->orWhereHas('checklist_category', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    });
                });
            });

            $checklists = $base
                ->orderByDesc($this->checklist_filter)
                ->paginate(10);

            return view('livewire.checklists.index', compact('checklists'));
       
    }
}
