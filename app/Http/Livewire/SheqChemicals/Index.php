<?php

namespace App\Http\Livewire\SheqChemicals;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqChemical;
use App\Models\UnitsOfMeasure;
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
    public $hazard_filter = '';
    public $department_filter = '';

    public $departments;
    public $employees;
    public $unit_of_measures;

    public $sheq_chemical_id;
    public $name;
    public $trade_name;
    public $supplier;
    public $hazard_class;
    public $department_id;
    public $storage_location;
    public $quantity;
    public $unit_of_measure;
    public $sds_available = 0;
    public $sds_review_date;
    public $storage_bunded = 0;
    public $spill_kit_available = 0;
    public $incompatible_with;
    public $ppe_required;
    public $coordinator_id;
    public $licence_required;
    public $status = 'in_use';

    protected $rules = [
        'name' => 'required',
        'department_id' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->unit_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->name = "";
        $this->trade_name = "";
        $this->supplier = "";
        $this->hazard_class = "";
        $this->department_id = "";
        $this->storage_location = "";
        $this->quantity = "";
        $this->unit_of_measure = "";
        $this->sds_available = 0;
        $this->sds_review_date = "";
        $this->storage_bunded = 0;
        $this->spill_kit_available = 0;
        $this->incompatible_with = "";
        $this->ppe_required = "";
        $this->coordinator_id = "";
        $this->licence_required = "";
        $this->status = "in_use";
    }

    public function store(){
        $this->validate();

        $chemical = new SheqChemical;
        $chemical->user_id = Auth::user()->id;
        $this->fill_fields($chemical);
        $chemical->save();

        $this->dispatchBrowserEvent('hide-sheq_chemicalModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Chemical Created Successfully!!"
        ]);
    }

    private function fill_fields($chemical){
        $chemical->name = $this->name;
        $chemical->trade_name = $this->trade_name;
        $chemical->supplier = $this->supplier;
        $chemical->hazard_class = $this->hazard_class;
        $chemical->department_id = $this->department_id;
        $chemical->storage_location = $this->storage_location;
        $chemical->quantity = $this->quantity;
        $chemical->unit_of_measure = $this->unit_of_measure;
        $chemical->sds_available = $this->sds_available ? 1 : 0;
        $chemical->sds_review_date = $this->sds_review_date ?: Null;
        $chemical->storage_bunded = $this->storage_bunded ? 1 : 0;
        $chemical->spill_kit_available = $this->spill_kit_available ? 1 : 0;
        $chemical->incompatible_with = $this->incompatible_with;
        $chemical->ppe_required = $this->ppe_required;
        $chemical->coordinator_id = $this->coordinator_id ?: Null;
        $chemical->licence_required = $this->licence_required;
        $chemical->status = $this->status;
    }

    public function edit($id){
        $chemical = SheqChemical::find($id);
        $this->sheq_chemical_id = $chemical->id;
        $this->name = $chemical->name;
        $this->trade_name = $chemical->trade_name;
        $this->supplier = $chemical->supplier;
        $this->hazard_class = $chemical->hazard_class;
        $this->department_id = $chemical->department_id;
        $this->storage_location = $chemical->storage_location;
        $this->quantity = $chemical->quantity;
        $this->unit_of_measure = $chemical->unit_of_measure;
        $this->sds_available = $chemical->sds_available;
        $this->sds_review_date = $chemical->sds_review_date ? Carbon::parse($chemical->sds_review_date)->format('Y-m-d') : Null;
        $this->storage_bunded = $chemical->storage_bunded;
        $this->spill_kit_available = $chemical->spill_kit_available;
        $this->incompatible_with = $chemical->incompatible_with;
        $this->ppe_required = $chemical->ppe_required;
        $this->coordinator_id = $chemical->coordinator_id;
        $this->licence_required = $chemical->licence_required;
        $this->status = $chemical->status;
        $this->dispatchBrowserEvent('show-sheq_chemicalEditModal');
    }

    public function update(){
        $this->validate();

        $chemical = SheqChemical::find($this->sheq_chemical_id);
        $this->fill_fields($chemical);
        $chemical->update();

        $this->dispatchBrowserEvent('hide-sheq_chemicalEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Chemical Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_chemical_id = $id;
        $this->dispatchBrowserEvent('show-sheq_chemicalDeleteModal');
    }

    public function destroy(){
        $chemical = SheqChemical::find($this->sheq_chemical_id);
        if ($chemical) {
            $chemical->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_chemicalDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Chemical Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqChemical::query()->with(['department','coordinator']);

        if ($this->hazard_filter) {
            $query->where('hazard_class', $this->hazard_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('name','like',$search)
                  ->orWhere('trade_name','like',$search)
                  ->orWhere('supplier','like',$search)
                  ->orWhere('storage_location','like',$search);
            });
        }

        $sheq_chemicals = $query->orderBy('name','asc')->paginate(10);

        return view('livewire.sheq-chemicals.index',[
            'sheq_chemicals' => $sheq_chemicals
        ]);
    }
}
