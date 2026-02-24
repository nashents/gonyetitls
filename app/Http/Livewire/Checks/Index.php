<?php

namespace App\Http\Livewire\Checks;
use App\Models\Check;
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
    private $checks;
    public $name;
    public $check_id;
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
        'name' => 'required|unique:checks,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
      
        $this->validate();
        
        $check = new Check;
        $check->user_id = Auth::user()->id;
        $check->name = $this->name;
        $check->save();

        $this->dispatchBrowserEvent('hide-checkModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Recruitment Check Created Successfully!!"
        ]);

   
    }

    public function edit($id){

    $check = Check::find($id);
    $this->name = $check->name;
    $this->check_id = $check->id;
    $this->dispatchBrowserEvent('show-checkEditModal');

    }


    public function update()
    {
        if ($this->check_id) {
       

            $check = Check::find($this->check_id);
            $check->name = $this->name;
            $check->check_number = $this->check_number;
            $check->update();

            $this->dispatchBrowserEvent('hide-checkEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Check Updated Successfully!!"
            ]);
            // return redirect()->route('checks.index');
       

        }
    }


    public function render()
    {
        $search = trim($this->search);

        $query = Check::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%");
                });
            })
        ->orderBy('name', 'asc')
        ->paginate(10);

        return view('livewire.checks.index',[
            'checks' => $query
        ]);
    }
}
