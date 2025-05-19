<?php

namespace App\Http\Livewire\Works;


use App\Models\Work;
use Livewire\Component;
use App\Models\Category;
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
    private $works;
    public $work_id;
    public $status;
    public $description;
    public $user_id;
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


    public function mount(){
        $this->resetPage();
    }

  
    private function resetInputFields(){
        $this->description = [];
        $this->inputs = [];
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'description' => 'required',
    ];

 

    public function store(){

        $this->validate();
        
        if (isset($this->description)) {
            foreach ($this->description as $key => $value) {
                $work = new Work;
                $work->user_id = Auth::user()->id;
                if (isset($this->description[$key])) {
                    $work->description = $this->description[$key];
                }
                $work->status = 1;
                $work->save();
            }   
        }
      
        $this->dispatchBrowserEvent('hide-workModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Work(s) Added Successfully!!"
        ]);
       
    }

    public function edit($id){
    $work = Work::find($id);
    $this->description = $work->description;
    $this->status = $work->status;
    $this->work_id = $work->id;
    $this->dispatchBrowserEvent('show-workEditModal');

    }

    public function update()
    {
        if ($this->work_id) {
            $work = Work::find($this->work_id);
            $work->description = $this->description;
            $work->status = $this->status;
            $work->save();

        $this->dispatchBrowserEvent('hide-workEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Work Updated Successfully!!"
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
            return view('livewire.works.index',[
                'works' => Work::query()
                ->where('description','like', '%'.$this->search.'%')
                ->orderBy('description','asc')->paginate(10),
            ]);
        }else {
            return view('livewire.works.index',[
                'works' => Work::orderBy('description','asc')->paginate(10),
            ]);
        }

      
    }
}
