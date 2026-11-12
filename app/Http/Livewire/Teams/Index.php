<?php

namespace App\Http\Livewire\Teams;


use App\Models\Team;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    public $team_id;
    public $employees;
    public $employee_id = [];
    public $departments;
    public $department_id = [];
    public $filter_department_id;
    public $status;
    public $name;


    public function mount(){
        $this->resetPage();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->departments = Department::orderBy('name','asc')->get();
    }


    private function resetInputFields(){
        $this->name = '';
        $this->employee_id = [];
        $this->department_id = [];
    }

    /** Multi-selects can post the blank placeholder option; drop it before syncing. */
    private function selectedIds($ids): array
    {
        return array_values(array_filter((array) $ids, 'filled'));
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
        $team->employees()->sync($this->selectedIds($this->employee_id));
        $team->departments()->sync($this->selectedIds($this->department_id));

        $this->dispatchBrowserEvent('hide-teamModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Team(s) Added Successfully!!"
        ]);

    }

    public function edit($id){
    $team = Team::find($id);
    $this->name = $team->name;
    $this->status = $team->status;
    $this->team_id = $team->id;
    $this->employee_id = $team->employees()->pluck('employees.id')->toArray();
    $this->department_id = $team->departments()->pluck('departments.id')->toArray();
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
             $team->employees()->sync($this->selectedIds($this->employee_id));
             $team->departments()->sync($this->selectedIds($this->department_id));

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

    public function updatingFilterDepartmentId()
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim((string) $this->search);

        $teams = Team::query()
            ->with('employees', 'departments')
            ->when($this->filter_department_id, fn ($q, $id) => $q->whereHas('departments', fn ($d) => $d->where('departments.id', $id)))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhereHas('departments', fn ($d) => $d->where('name', 'like', '%'.$search.'%'));
            }))
            ->orderBy('name', 'asc')->orderBy('id', 'asc')
            ->paginate(10);

        return view('livewire.teams.index', ['teams' => $teams]);
    }
}
