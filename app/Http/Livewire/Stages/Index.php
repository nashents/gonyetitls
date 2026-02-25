<?php

namespace App\Http\Livewire\stages;
use App\Models\Stage;
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
    private $stages;
    public $name;
    public $description;
    public $status = 1;
    public $stage_id;
    public $user_id;

    public function mount(){
       
    }
 
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->status = "";
        $this->description = "";
      
    }
    protected $rules = [
        'name' => 'required|unique:stages,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
      
        $this->validate();
        
        $stage = new Stage;
        $stage->user_id = Auth::user()->id;
        $stage->name = $this->name;
        $stage->description = $this->description;
        $stage->status = $this->status;
        $stage->save();

        $this->dispatchBrowserEvent('hide-stageModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Recruitment Stage Created Successfully!!"
        ]);

   
    }

    public function edit($id){

    $stage = stage::find($id);
    $this->name = $stage->name;
    $this->description = $stage->description;
    $this->status = $stage->status;
    $this->stage_id = $stage->id;
    $this->dispatchBrowserEvent('show-stageEditModal');

    }


    public function update()
    {
        if ($this->stage_id) {
       

            $stage = Stage::find($this->stage_id);
            $stage->name = $this->name;
            $stage->description = $this->description;
            $stage->status = $this->status;
            $stage->update();

            $this->dispatchBrowserEvent('hide-stageEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Stage Updated Successfully!!"
            ]);
            // return redirect()->route('stages.index');
       

        }
    }

       public function delete($id){

        $stage = Stage::find($id);
        $this->stage_id = $stage->id;
        $this->dispatchBrowserEvent('show-stageDeleteModal');

    }
    public function destroy(){

        $stage = Stage::find($this->stage_id);
        $stage->delete();
        $this->dispatchBrowserEvent('hide-stageDeleteModal');
        $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Recruitment Stage Deleted Successfully!!"
            ]);
    }

    public function render()
    {
        $search = trim($this->search);

        $query = Stage::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%");
                });
            })
        ->orderBy('name', 'asc')
        ->paginate(10);

        return view('livewire.stages.index',[
            'stages' => $query
        ]);
    }
}
