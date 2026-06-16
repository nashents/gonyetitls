<?php

namespace App\Http\Livewire\Qualifications;

use App\Models\Qualification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $qualifications;
    public $qualification_id;
    public $code;
    public $name;
    public $category;
    public $level;
    public $is_expiring = False;
    public $validity_months;
    public $description;
    public $user_id;

    public function mount(){
        $this->resetPage();
    }
    
      public function updatingSearch()
    {
        $this->resetPage();
    }

     private function resetInputFields(){
        $this->name = '';
        $this->code= '';
        $this->category = '';
        $this->level = '';
        $this->validity_months = '';
        $this->is_expiring = '';
        $this->description = '';
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:qualifications,name,NULL,id,deleted_at,NULL',
    ];

    

    public function store(){

        $this->validate();

        $qualification = new Qualification;
        $qualification->user_id = Auth::user()->id;
        $qualification->name = $this->name;
        $qualification->code = $this->code ?? Str::slug($this->name);
        $qualification->category = $this->category;
        $qualification->level = $this->level;
        $qualification->is_expiring = $this->is_expiring;
        $qualification->validity_months = $this->validity_months;
        $qualification->description = $this->description;
        $qualification->save();

        $this->dispatchBrowserEvent('hide-qualificationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Qualification Created Successfully!!"
        ]);
    }
  
    public function edit($id){
        $qualification = Qualification::find($id);
        $this->user_id = $qualification->user_id;
        $this->name = $qualification->name;
        $this->code = $qualification->code;
        $this->level = $qualification->level;
        $this->category = $qualification->category;
        $this->validity_months = $qualification->validity_months;
        $this->is_expiring = $qualification->is_expiring;
        $this->description = $qualification->description;
        $this->qualification_id = $qualification->id;
        $this->dispatchBrowserEvent('show-qualificationEditModal');

    }

    public function update(){

        $qualification = Qualification::find($this->qualification_id);
        $qualification->code = $this->code;
        $qualification->name = $this->name;
        $qualification->category = $this->category;
        $qualification->level = $this->level;
        $qualification->is_expiring = $this->is_expiring;
        $qualification->validity_months = $this->validity_months;
        $qualification->description = $this->description;
        $qualification->update();

        $this->dispatchBrowserEvent('hide-qualificationEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Qualification Updated Successfully!!"
        ]);
    }
    public function render()
    {

        if (filled($this->search)) {
             return view('livewire.qualifications.index',[
            'qualifications' => Qualification::where('name','LIKE', '%'.$this->search.'%')
                ->orWhere('code','LIKE', '%'.$this->search.'%')
                ->orWhere('level','LIKE', '%'.$this->search.'%')
                ->orWhere('category','LIKE', '%'.$this->search.'%')
                ->orWhere('description','LIKE', '%'.$this->search.'%')
                ->orWhere('validity_months','LIKE', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.qualifications.index',[
            'qualifications' => Qualification::orderBy('name','asc')->paginate(10)
            ]);
        }
       
    }
}
