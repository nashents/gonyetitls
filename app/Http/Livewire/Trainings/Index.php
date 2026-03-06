<?php

namespace App\Http\Livewire\Trainings;


use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search','search_from','search_to'];
    public $search_from;
    public $search_to;
    protected $trainings;
    public $training_id;
    public $training;
    public $training_items;
    public $training_item_id;
    public $training_item;
    public $employees;
    public $employee_id;
    public $date;
    public $participation;
    public $comments;
    public $day_event = false;
    public $from;
    public $to;
  

  

    public function mount(){
       
        $this->training_items = TrainingItem::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
        'training_item_id' => 'required',
        'employee_id' => 'required',
    ];

    private function resetInputFields(){
      
        $this->training_item_id = '';
        $this->employee_id = '';
        $this->date = '';
        $this->participation = '';
        $this->comments = '';
        $this->day_event = false;
        $this->from = '';
        $this->to = '';
     
    }

    public function refresh($category){

        if($category == "training_items"){
            $this->training_items = TrainingItem::orderBy('name','asc')->where('status',1)->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Items Refreshed Successfully!!."
            ]);
        }
       
        elseif($category == "employees"){
            $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Employees Refreshed Successfully!!."
            ]);
        }
    }

    public function store(){

        DB::transaction(function () {
            $training = new Training;
            $training->user_id = Auth::user()->id;
            $training->date = $this->date;
            $training->from = $this->from;
            $training->to = $this->to;
            $training->day_event = $this->day_event;
            $training->training_item_id = $this->training_item_id;
            $training->employee_id = $this->employee_id;
            $training->participation = $this->participation;
            $training->comments = $this->comments;
            $training->save();

            $this->dispatchBrowserEvent('hide-trainingModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Record Created Successfully!!"
            ]);

        });
    }

    public function edit($id){
    $training = Training::find($id);
    $this->date = $training->date;
    $this->training_item_id = $training->training_item_id;
    $this->employee_id = $training->employee_id;
    $this->participation = $training->participation;
    $this->from = $training->from;
    $this->to = $training->to;
    $this->day_event = $training->day_event;
    $this->comments = $training->comments;
    $this->training_id = $training->id;
    $this->dispatchBrowserEvent('show-trainingEditModal');

    }


    public function update()
    {
        if ($this->training_id) {
            DB::transaction(function () {
                $training = Training::find($this->training_id);
                $training->date = $this->date;
                $training->training_item_id = $this->training_item_id;
                $training->employee_id = $this->employee_id;
                $training->from = $this->from;
                $training->to = $this->to;
                $training->day_event = $this->day_event;
                $training->comments = $this->comments;
                $training->participation = $this->participation;
                $training->update();

                $this->dispatchBrowserEvent('hide-trainingEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Training Record Updated Successfully!!"
                ]);
            });
        }
    }


    public function render()
    {
       
       $query = Training::query()
        ->with(['employee', 'training_item']);

    // Default filter: current year
    if (!empty($this->search_from) && !empty($this->search_to)) {
        $query->whereBetween('created_at', [$this->search_from, $this->search_to]);
    } else {
        $query->whereYear('created_at', now()->year);
    }

    // Search filter
    if (!empty($this->search)) {
        $search = '%' . trim($this->search) . '%';

        $query->where(function ($q) use ($search) {
            $q->where('date', 'like', $search)
              ->orWhere('comments', 'like', $search)
              ->orWhereHas('employee', function ($q) use ($search) {
                  $q->where('name', 'like', $search)
                    ->orWhere('surname', 'like', $search)
                    ->orWhereRaw("CONCAT(name, ' ', surname) like ?", [$search]);
              })
              ->orWhereHas('training_item', function ($q) use ($search) {
                  $q->where('name', 'like', $search);
              });
        });
    }

    $trainings = $query->orderBy('created_at','desc')->paginate(10);

    return view('livewire.trainings.index', [
        'trainings' => $trainings,
    ]);
    }
}
