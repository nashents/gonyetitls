<?php

namespace App\Http\Livewire\ServiceTypes;

use Livewire\Component;
use App\Models\ServiceType;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $service_types;
    public $name;
    public $service_type_id;
    public $user_id;

    public function mount(){
      
    }

    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:service_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){


        $service_type = new ServiceType;
        $service_type->user_id = Auth::user()->id;
        $service_type->name = $this->name;
        $service_type->save();
        $this->dispatchBrowserEvent('hide-service_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Type Created Successfully!!"
        ]);
      

    }

    public function edit($id){

    $service_type = ServiceType::find($id);
    $this->user_id = $service_type->user_id;
    $this->name = $service_type->name;
    $this->service_type_id = $service_type->id;
    $this->dispatchBrowserEvent('show-service_typeEditModal');

    }



    public function update()
    {
        if ($this->service_type_id) {
        
            $service_type = ServiceType::find($this->service_type_id);
            $service_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);
            $this->dispatchBrowserEvent('hide-service_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Service Type Updated Successfully!!"
            ]);
      
        }
    }


    public function render()
    {
      
        $base = ServiceType::query()
        ->with([ 'inspection_services.inspection_type']);

        $service_types = $base
            ->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                    ->orWhereHas('inspection_services', function ($sub) use ($term) {
                        $sub->whereHas('inspection_type', function ($qsub) use ($term) {
                            $qsub->where('name', 'like', $term);
                        });
                    });
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('livewire.service-types.index', [
            'service_types' => $service_types,
        ]);
    }
}
