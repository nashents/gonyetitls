<?php

namespace App\Http\Livewire\ServiceTypes;

use Livewire\Component;
use App\Models\ServiceType;
use Livewire\WithPagination;
use App\Models\InspectionType;
use App\Models\InspectionGroup;
use Illuminate\Validation\Rule;
use App\Models\InspectionService;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
   
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    public $inspection_types;
    public $inspection_type_id;
    public $inspection_groups;
    public $inspection_group_id;
    private $inspection_services;
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
        $this->inspection_groups = InspectionGroup::orderBy('name','asc')->get();
        $this->inspection_types = InspectionType::orderBy('name','asc')->get();
    }


    public function store(){

        $typeName = optional(\App\Models\ServiceType::find($this->service_type_id))->name ?? 'this job type';
        
        $this->validate([
            // the array itself
            'inspection_type_id' => ['required', 'array'],
            // prevent duplicates within the submitted array too
            'inspection_type_id.*' => [
                'required',
                'distinct',
                Rule::unique('inspection_services', 'inspection_type_id') // 👈 explicit column
                    ->where(fn ($q) => $q->where('service_type_id', $this->service_type_id)
                                        ->whereNull('deleted_at') // add if the table is soft-deleting
                                        // ->where('company_id', $this->company_id) // add if multi-tenant
                    ),
            ],

            'inspection_group_id'   => ['nullable', 'array'],
            'inspection_group_id.*' => ['nullable'],
        ],
    
            // Custom messages
        [
            'inspection_type_id.required'     => 'Add at least one inspection item.',
            'inspection_type_id.*.required'   => 'Select an inspection item.',
            'inspection_type_id.*.distinct'   => 'You have duplicate inspection items in the list.',
            'inspection_type_id.*.unique'     => "This inspection item is already linked to {$typeName}.",

            'inspection_group_id.nullable'    => 'Add at least one item group.',
            'inspection_group_id.*.nullable'  => 'Select an item group.',
        ],

        // (Optional) Nicely formatted attribute names
        [
            'inspection_type_id.*'  => 'inspection item',
            'inspection_group_id.*' => 'inspection item group',
        ]

    );

        if (isset($this->inspection_type_id)) {
            foreach ($this->inspection_type_id as $key => $value) {

                $inspection_service = new InspectionService;
                $inspection_service->user_id = Auth::user()->id;
                $inspection_service->service_type_id = $this->service_type_id;
               
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

       public function refresh($category){

        if($category == "inspection_types"){
            $this->inspection_types = InspectionType::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Items Refreshed Successfully!!."
            ]);
        }
        elseif($category == "inspection_groups"){
            $this->inspection_groups = InspectionGroup::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Groups Refreshed Successfully!!."
            ]);
        }
    }

    public function edit($id){
        $inspection_service = InspectionService::find($id);
        $this->service_type_id = $inspection_service->service_type_id;
        $this->inspection_group_id = $inspection_service->inspection_group_id;
        $this->inspection_type_id = $inspection_service->inspection_type_id;
        $this->inspection_service_id = $inspection_service->id;
        $this->dispatchBrowserEvent('show-inspection_serviceEditModal');
    }

    public function update(){

        if (isset($this->inspection_service_id)) {
            $inspection_service = InspectionService::find($this->inspection_service_id);
            $inspection_service->service_type_id = $this->service_type_id;
            $inspection_service->inspection_group_id = $this->inspection_group_id;
            $inspection_service->inspection_type_id = $this->inspection_type_id;
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

             $base = InspectionService::query()
            ->with(['service_type', 'inspection_type', 'inspection_group'])
            ->where('inspection_services.service_type_id', $this->service_type_id)
            ->leftJoin(
                'inspection_groups',
                'inspection_services.inspection_group_id',
                '=',
                'inspection_groups.id'
            )
            ->select('inspection_services.*')
            ->orderByRaw('inspection_groups.name IS NULL') // nulls last
            ->orderBy('inspection_groups.name', 'asc');

        if (filled($this->search)) {
            $term = '%' . $this->search . '%';

            $base->where(function ($q) use ($term) {
                $q->whereHas('inspection_type', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                })->orWhereHas('inspection_group', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                });
            });
        }

        return view('livewire.service-types.show', [
            'inspection_services' => $base->paginate(10),
        ]);
       
       
    }
}
