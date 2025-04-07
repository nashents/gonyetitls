<?php

namespace App\Http\Livewire\ServiceTypes;

use Livewire\Component;
use App\Models\ServiceType;
use App\Models\InspectionType;
use App\Models\InspectionGroup;
use App\Models\InspectionService;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
   
    public $inspection_types;
    public $inspection_type_id;
    public $inspection_groups;
    public $inspection_group_id;
    public $inspection_services;
    public $inspection_service_id;
    public $service_type;
    public $service_type_id;
    public $category;


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

    private function resetInputFields(){
        $this->inspection_group_id = "" ;
        $this->inspection_type_id = "" ;
    }



    public function mount($id){
        $this->service_type_id = $id;
        $this->service_type = ServiceType::find($id);
        $this->inspection_services = InspectionService::where('service_type_id', $this->service_type_id)->get();
        $this->inspection_groups = InspectionGroup::orderBy('name','asc')->get();
        $this->inspection_types = InspectionType::orderBy('name','asc')->get();
    }

    public function store(){
        if (isset($this->inspection_type_id)) {
            foreach ($this->inspection_type_id as $key => $value) {
                $inspection_service = new InspectionService;
                $inspection_service->user_id = Auth::user()->id;
                $inspection_service->service_type_id = $this->service_type_id;
                if (isset($this->category[$key])) {
                    $inspection_service->category = $this->category[$key];
                }
                if (isset($this->inspection_group_id[$key])) {
                    $inspection_service->inspection_group_id = $this->inspection_group_id[$key];
                }
                if (isset($this->inspection_type_id[$key])) {
                    $inspection_service->inspection_type_id = $this->inspection_type_id[$key];
                }

                $inspection_service->save();
            }

            $this->dispatchBrowserEvent('hide-inspection_serviceModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Category Checklist Item Added Successfully!!"
            ]);
        }
    }

    public function edit($id){
        $inspection_service = InspectionService::find($id);
        $this->service_type_id = $inspection_service->service_type_id;
        $this->inspection_group_id = $inspection_service->inspection_group_id;
        $this->inspection_type_id = $inspection_service->inspection_type_id;
        $this->category = $inspection_service->category;
        $this->inspection_service_id = $inspection_service->id;
        $this->dispatchBrowserEvent('show-inspection_serviceEditModal');
    }

    public function update(){
        if (isset($this->inspection_service_id)) {
            $inspection_service = InspectionService::find($this->inspection_service_id);
            $inspection_service->service_type_id = $this->service_type_id;
            $inspection_service->inspection_group_id = $this->inspection_group_id;
            $inspection_service->inspection_type_id = $this->inspection_type_id;
            $inspection_service->category = $this->category;
            $inspection_service->update();

            $this->dispatchBrowserEvent('hide-inspection_serviceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Category Checklist Item Updated Successfully!!"
            ]);
        }
    }

    public function render()
    {
        $this->inspection_services = InspectionService::where('service_type_id', $this->service_type_id)->get();
        return view('livewire.service-types.show',[
            'inspection_services' => $this->inspection_services
        ]);
    }
}
