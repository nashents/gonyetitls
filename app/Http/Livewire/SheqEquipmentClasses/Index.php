<?php

namespace App\Http\Livewire\SheqEquipmentClasses;

use App\Models\SheqEquipmentClass;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $sheq_equipment_class_id;
    public $name;
    public $inspection_frequency_days;
    public $requires_color_code = 0;
    public $requires_load_test = 0;
    public $description;

    protected $rules = [
        'name' => 'required',
    ];

    private function resetInputFields(){
        $this->name = "";
        $this->inspection_frequency_days = "";
        $this->requires_color_code = 0;
        $this->requires_load_test = 0;
        $this->description = "";
    }

    public function store(){
        $this->validate();

        $class = new SheqEquipmentClass;
        $class->user_id = Auth::user()->id;
        $class->name = $this->name;
        $class->inspection_frequency_days = $this->inspection_frequency_days ?: Null;
        $class->requires_color_code = $this->requires_color_code ? 1 : 0;
        $class->requires_load_test = $this->requires_load_test ? 1 : 0;
        $class->description = $this->description;
        $class->save();

        $this->dispatchBrowserEvent('hide-sheq_equipment_classModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Equipment Class Created Successfully!!"
        ]);
    }

    public function edit($id){
        $class = SheqEquipmentClass::find($id);
        $this->sheq_equipment_class_id = $class->id;
        $this->name = $class->name;
        $this->inspection_frequency_days = $class->inspection_frequency_days;
        $this->requires_color_code = $class->requires_color_code;
        $this->requires_load_test = $class->requires_load_test;
        $this->description = $class->description;
        $this->dispatchBrowserEvent('show-sheq_equipment_classEditModal');
    }

    public function update(){
        $this->validate();

        $class = SheqEquipmentClass::find($this->sheq_equipment_class_id);
        $class->name = $this->name;
        $class->inspection_frequency_days = $this->inspection_frequency_days ?: Null;
        $class->requires_color_code = $this->requires_color_code ? 1 : 0;
        $class->requires_load_test = $this->requires_load_test ? 1 : 0;
        $class->description = $this->description;
        $class->update();

        $this->dispatchBrowserEvent('hide-sheq_equipment_classEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Equipment Class Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_equipment_class_id = $id;
        $this->dispatchBrowserEvent('show-sheq_equipment_classDeleteModal');
    }

    public function destroy(){
        $class = SheqEquipmentClass::find($this->sheq_equipment_class_id);
        if ($class) {
            $class->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_equipment_classDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Equipment Class Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqEquipmentClass::query()->withCount('equipment');
        if ($this->search) {
            $query->where('name','like','%'.$this->search.'%');
        }
        $sheq_equipment_classes = $query->orderBy('name','asc')->paginate(10);

        return view('livewire.sheq-equipment-classes.index',[
            'sheq_equipment_classes' => $sheq_equipment_classes
        ]);
    }
}
