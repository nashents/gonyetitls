<?php

namespace App\Http\Livewire\InspectionServices;

use Livewire\Component;
use App\Models\ServiceType;
use Livewire\WithPagination;
use App\Models\InspectionType;
use App\Models\InspectionGroup;
use App\Models\InspectionService;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $inspection_services;
    public $inspection_service_id;
    public $service_types;
    public $service_type_id;
    public $inspection_types;
    public $inspection_type_id;
    public $inspection_groups;
    public $selectedInspectionGroup;
    public $user_id;
    public $category;

    public function mount(){
        $this->service_types = ServiceType::all();
        $this->inspection_types = InspectionType::all();
        $this->inspection_groups = InspectionGroup::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'service_type_id' => 'required',
        'selectedInspectionGroup' => 'required',
        'inspection_type_id' => 'required',
        'category' => 'required',
    ];

    private function resetInputFields(){
        $this->service_type_id = '';
        $this->selectedInspectionGroup = '';
        $this->inspection_type_id = '';
        $this->category = '';
    }

    public function updatedSelectedInspectionGroup($id){
        if (!is_null($id)) {
            $this->inspection_types = InspectionType::where('inspection_group_id',$id)->get();
        }
    }

    public function store(){
        try{
            $inspection_service = new InspectionService;
            $inspection_service->service_type_id = $this->service_type_id;
            $inspection_service->inspection_group_id = $this->selectedInspectionGroup;
            $inspection_service->inspection_type_id = $this->inspection_type_id;
            $inspection_service->category = $this->category;
            $inspection_service->save();
            $this->dispatchBrowserEvent('hide-inspection_typeModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Service Checklist Item Created Successfully!!"
            ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating Service Checklist Item!!"
        ]);
    }
    }

    public function edit($id){

    $inspection_service = InspectionService::find($id);
    $this->selectedInspectionGroup = $inspection_service->inspection_group_id;
    $this->service_type_id = $inspection_service->service_type_id;
    $this->inspection_type_id = $inspection_service->inspection_type_id;
    $this->inspection_service_id = $inspection_service->id;
    $this->category = $inspection_service->category;
    $this->dispatchBrowserEvent('show-inspection_serviceEditModal');

    }
   

    public function update()
    {
        if ($this->inspection_service_id) {
            try{
            $inspection_service = InspectionService::find($this->inspection_service_id);
            $inspection_service->inspection_group_id = $this->selectedInspectionGroup;
            $inspection_service->service_type_id = $this->service_type_id;
            $inspection_service->inspection_type_id = $this->inspection_type_id;
            $inspection_service->category = $this->category;
            $inspection_service->update();

            $this->dispatchBrowserEvent('hide-inspection_serviceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Service Checklist Item Updated Successfully!!"
            ]);


            // return redirect()->route('inspection_types.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-inspection_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating Service Checklist Item!!"
            ]);
          }
        }
    }


    public function render()
    {
        return view('livewire.inspection-services.index',[
            'inspection_services' => InspectionService::orderBy('created_at','desc')->paginate(10),
        ]);
    }
}
