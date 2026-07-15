<?php

namespace App\Http\Livewire\SheqEquipment;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqEquipment;
use App\Models\SheqEquipmentClass;
use App\Models\SheqEquipmentInspection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $class_filter = '';
    public $department_filter = '';
    public $status_filter = '';

    public $departments;
    public $employees;
    public $classes;

    public $sheq_equipment_id;
    public $sheq_equipment_class_id;
    public $equipment_number;
    public $description;
    public $department_id;
    public $location;
    public $swl;
    public $certificate_expiry;
    public $current_color_code;
    public $status = 'in_service';

    public $inspect_equipment_id;
    public $inspector_id;
    public $inspection_date;
    public $result;
    public $color_code_applied;
    public $defects;
    public $comments;

    protected $rules = [
        'sheq_equipment_class_id' => 'required',
        'equipment_number' => 'required',
        'department_id' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->classes = SheqEquipmentClass::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->sheq_equipment_class_id = "";
        $this->equipment_number = "";
        $this->description = "";
        $this->department_id = "";
        $this->location = "";
        $this->swl = "";
        $this->certificate_expiry = "";
        $this->current_color_code = "";
        $this->status = "in_service";
        $this->inspector_id = "";
        $this->inspection_date = "";
        $this->result = "";
        $this->color_code_applied = "";
        $this->defects = "";
        $this->comments = "";
    }

    public function store(){
        $this->validate();

        $equipment = new SheqEquipment;
        $equipment->user_id = Auth::user()->id;
        $this->fill_fields($equipment);
        $equipment->save();

        $this->dispatchBrowserEvent('hide-sheq_equipmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Equipment Created Successfully!!"
        ]);
    }

    private function fill_fields($equipment){
        $equipment->sheq_equipment_class_id = $this->sheq_equipment_class_id;
        $equipment->equipment_number = $this->equipment_number;
        $equipment->description = $this->description;
        $equipment->department_id = $this->department_id;
        $equipment->location = $this->location;
        $equipment->swl = $this->swl;
        $equipment->certificate_expiry = $this->certificate_expiry ?: Null;
        $equipment->current_color_code = $this->current_color_code;
        $equipment->status = $this->status;
    }

    public function edit($id){
        $equipment = SheqEquipment::find($id);
        $this->sheq_equipment_id = $equipment->id;
        $this->sheq_equipment_class_id = $equipment->sheq_equipment_class_id;
        $this->equipment_number = $equipment->equipment_number;
        $this->description = $equipment->description;
        $this->department_id = $equipment->department_id;
        $this->location = $equipment->location;
        $this->swl = $equipment->swl;
        $this->certificate_expiry = $equipment->certificate_expiry ? Carbon::parse($equipment->certificate_expiry)->format('Y-m-d') : Null;
        $this->current_color_code = $equipment->current_color_code;
        $this->status = $equipment->status;
        $this->dispatchBrowserEvent('show-sheq_equipmentEditModal');
    }

    public function update(){
        $this->validate();

        $equipment = SheqEquipment::find($this->sheq_equipment_id);
        $this->fill_fields($equipment);
        $equipment->update();

        $this->dispatchBrowserEvent('hide-sheq_equipmentEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Equipment Updated Successfully!!"
        ]);
    }

    public function inspect($id){
        $this->inspect_equipment_id = $id;
        $this->inspection_date = Carbon::today()->format('Y-m-d');
        $this->dispatchBrowserEvent('show-sheq_equipmentInspectModal');
    }

    public function storeInspection(){
        $this->validate([
            'inspection_date' => 'required',
            'result' => 'required',
        ]);

        $inspection = new SheqEquipmentInspection;
        $inspection->sheq_equipment_id = $this->inspect_equipment_id;
        $inspection->user_id = Auth::user()->id;
        $inspection->inspector_id = $this->inspector_id ?: Null;
        $inspection->inspection_date = $this->inspection_date;
        $inspection->result = $this->result;
        $inspection->color_code_applied = $this->color_code_applied;
        $inspection->defects = $this->defects;
        $inspection->comments = $this->comments;
        $inspection->save();

        $equipment = SheqEquipment::find($this->inspect_equipment_id);
        $equipment->last_inspection_date = $this->inspection_date;
        if ($equipment->equipment_class && $equipment->equipment_class->inspection_frequency_days) {
            $equipment->next_inspection_date = Carbon::parse($this->inspection_date)
                ->addDays($equipment->equipment_class->inspection_frequency_days)
                ->format('Y-m-d');
        }
        if ($this->color_code_applied) {
            $equipment->current_color_code = $this->color_code_applied;
        }
        if ($this->result == 'fail') {
            $equipment->status = 'quarantined';
        }
        $equipment->update();

        $this->dispatchBrowserEvent('hide-sheq_equipmentInspectModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inspection Recorded Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_equipment_id = $id;
        $this->dispatchBrowserEvent('show-sheq_equipmentDeleteModal');
    }

    public function destroy(){
        $equipment = SheqEquipment::find($this->sheq_equipment_id);
        if ($equipment) {
            $equipment->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_equipmentDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Equipment Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqEquipment::query()->with(['equipment_class','department','inspections']);

        if ($this->class_filter) {
            $query->where('sheq_equipment_class_id', $this->class_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->status_filter == 'overdue') {
            $query->whereDate('next_inspection_date','<', Carbon::today());
        } elseif ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('equipment_number','like',$search)
                  ->orWhere('description','like',$search)
                  ->orWhere('location','like',$search);
            });
        }

        $sheq_equipment = $query->orderBy('equipment_number','asc')->paginate(10);

        return view('livewire.sheq-equipment.index',[
            'sheq_equipment' => $sheq_equipment
        ]);
    }
}
