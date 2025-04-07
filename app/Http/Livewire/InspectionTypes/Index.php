<?php

namespace App\Http\Livewire\InspectionTypes;

use Livewire\Component;
use App\Models\ServiceType;
use Livewire\WithPagination;
use App\Models\InspectionType;
use App\Models\InspectionGroup;
use App\Models\InspectionService;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $inspection_types;
    public $inspection_type_id;
    public $inspection_groups;
    public $inspection_group_id;
    public $user_id;
    public $name;

    public function mount(){
      
        $this->inspection_groups = InspectionGroup::all();
        
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'inspection_group_id' => 'required',
        'name' => 'required|unique:inspection_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
         $this->name = '';
         $this->inspection_group_id = '';
    }

    public function store(){
        try{

            $inspection_type = new InspectionType;
            $inspection_type->user_id = Auth::user()->id;
            $inspection_type->name = $this->name;
            $inspection_type->inspection_group_id = $this->inspection_group_id;
            $inspection_type->save();

            $this->dispatchBrowserEvent('hide-inspection_typeModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Inspection Item Created Successfully!!"
            ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating Inspection Item!!"
        ]);
    }
    }

 
    public function edit($id){
    $inspection_type = InspectionType::find($id);
    $this->name = $inspection_type->name;
    $this->inspection_group_id = $inspection_type->inspection_group_id;
    $this->inspection_type_id = $inspection_type->id;
    $this->dispatchBrowserEvent('show-inspection_typeEditModal');

    }



    public function update()
    {
        if ($this->inspection_type_id) {
            try{
            $inspection_type = InspectionType::find($this->inspection_type_id);
            $inspection_type->name = $this->name;
            $inspection_type->inspection_group_id = $this->inspection_group_id;
            $inspection_type->update();

            $this->dispatchBrowserEvent('hide-inspection_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Inspection Item Updated Successfully!!"
            ]);


            // return redirect()->route('inspection_types.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-inspection_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating Inspection Item!!"
            ]);
          }
        }
    }

    public function render()
    {
        return view('livewire.inspection-types.index',[
            'inspection_types' => InspectionType::orderBy('created_at','desc')->paginate(10),
        ]);
    }
}
