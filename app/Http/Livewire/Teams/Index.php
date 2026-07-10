<?php

namespace App\Http\Livewire\Teams;


use App\Models\Team;
use Livewire\Component;
use App\Models\Category;
use App\Models\Employee;
use Livewire\WithPagination;
use App\Models\CategoryValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $teams;
    public $team_id;
    public $employees;
    public $employee_id = [];
    public $status;
    public $name;
  

    public function mount(){
        $this->resetPage();
        $this->employees = Employee::orderBy('name','asc')->orderBy('name','asc')->get();
    }

   
    private function resetInputFields(){
        $this->name = '';
        $this->employee_id = [];
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:teams,name,NULL,id,deleted_at,NULL',
    ];

 

    public function store(){
        $this->validate();
        $team = new Team;
        $team->user_id = Auth::user()->id;
        $team->name = $this->name;
        $team->status = 1;
        $team->save();
        $team->employees()->sync($this->employee_id);
      
        $this->dispatchBrowserEvent('hide-teamModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Team(s) Added Successfully!!"
        ]);
     
    }

    public function edit($id){
    $team = Team::find($id);
    $this->user_id = $team->user_id;
    $this->name = $team->name;
    $this->selectedCategory = $team->category_id;
    $this->category_value_id = $team->category_value_id;
    $this->status = $team->status;
    $this->team_id = $team->id;
    $this->employee_id = $team->employees()->pluck('employees.id')->toArray();
    $this->dispatchBrowserEvent('show-teamEditModal');

    }

    public function update()
    {
        if ($this->team_id) {
            $team = Team::find($this->team_id);
            $team->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'status' => $this->status,
            ]);
             $team->employees()->detach();
             $team->employees()->sync($this->employee_id);

        $this->dispatchBrowserEvent('hide-teamEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Team Updated Successfully!!"
        ]);

        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
       
        if (isset($this->search)) {
            return view('livewire.teams.index',[
                'teams' => Team::query()->with('category','category_value')
                ->where('name','like', '%'.$this->search.'%')
                ->orWhereHas('category', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('category_value', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10),
            ]);
        }else {
            return view('livewire.teams.index',[
                'teams' => Team::orderBy('name','asc')->paginate(10),
            ]);
        }

      
    }
}
