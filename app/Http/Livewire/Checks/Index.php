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
    public $description;
    public $status = 1;
    public $check_id;
    public $user_id;

    public function mount(){
       
    }
 
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->description = "";
        $this->status = "";
      
    }
    protected $rules = [
        'name' => 'required|unique:checks,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
      
        $this->validate();
        
        $check = new Check;
        $check->user_id = Auth::user()->id;
        $check->name = $this->name;
        $check->description = $this->description;
        $check->status = $this->status;
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
        $this->description = $check->description;
        $this->status = $check->status;
        $this->check_id = $check->id;
        $this->dispatchBrowserEvent('show-checkEditModal');

    }
    public function delete($id){

        $check = Check::find($id);
        $this->check_id = $check->id;
        $this->dispatchBrowserEvent('show-checkDeleteModal');

    }
    public function destroy(){

        $check = Check::find($this->check_id);
        $check->delete();
        $this->dispatchBrowserEvent('hide-checkDeleteModal');
        $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Recruitment Check Deleted Successfully!!"
            ]);
    }


    public function update()
    {
        if ($this->check_id) {
       

            $check = Check::find($this->check_id);
            $check->name = $this->name;
            $check->description = $this->description;
            $check->status = $this->status;
            $check->update();

            $this->dispatchBrowserEvent('hide-checkEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Check Updated Successfully!!"
            ]);

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
