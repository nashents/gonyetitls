<?php

namespace App\Http\Livewire\Criterions;
use App\Models\Criterion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $criterions;
    public $name;
    public $criterion_id;
    public $user_id;

    public function mount(){
       
    }
 
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
      
    }
    protected $rules = [
        'name' => 'required|unique:criterions,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
      
        $this->validate();
        
        $criterion = new Criterion;
        $criterion->user_id = Auth::user()->id;
        $criterion->name = $this->name;
        $criterion->save();

        $this->dispatchBrowserEvent('hide-criterionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Recruitment Criterion Created Successfully!!"
        ]);

   
    }

    public function edit($id){

    $criterion = criterion::find($id);
    $this->name = $criterion->name;
    $this->criterion_id = $criterion->id;
    $this->dispatchBrowserEvent('show-criterionEditModal');

    }


    public function update()
    {
        if ($this->criterion_id) {
       

            $criterion = Criterion::find($this->criterion_id);
            $criterion->name = $this->name;
            $criterion->criterion_number = $this->criterion_number;
            $criterion->update();

            $this->dispatchBrowserEvent('hide-criterionEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Recruitment Criterion Updated Successfully!!"
            ]);
            // return redirect()->route('criterions.index');
       

        }
    }


    public function render()
    {
        $search = trim($this->search);

        $query = Criterion::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%");
                });
            })
        ->orderBy('name', 'asc')
        ->paginate(10);

        return view('livewire.criterions.index',[
            'criterions' => $query
        ]);
    }
}
