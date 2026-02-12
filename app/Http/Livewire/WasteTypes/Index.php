<?php

namespace App\Http\Livewire\WasteTypes;

use Livewire\Component;
use App\Models\WasteType;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\WasteTypesExport;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $waste_types;
    public $name;
    public $generation_area;
    public $general_composition;
    public $impact;
    public $control_methods;
    public $category;
    public $waste_type_id;

    public function mount(){
        
    }
    public function exportwaste_typesCSV(Excel $excel){

        return $excel->download(new WasteTypesExport, 'waste_types.csv', Excel::CSV);
    }
    public function exportwaste_typesPDF(Excel $excel){

        return $excel->download(new WasteTypesExport, 'waste_types.pdf', Excel::DOMPDF);
    }
    public function exportwaste_typesExcel(Excel $excel){

        return $excel->download(new WasteTypesExport, 'waste_types.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->category = "";
        $this->general_composition = "";
        $this->generation_area = "";
        $this->impact = "";
        $this->control_methods = "";
    }
    protected $rules = [
        'name' => 'required',
        'category' => 'required',
        'general_composition' => 'required',
        'generation_area' => 'required',
        'impact' => 'required',
        'control_methods' => 'required',
    ];

    public function store(){
     
        
        $this->validate();
        
        $waste_type = new WasteType;
        $waste_type->user_id = Auth::user()->id;
        $waste_type->name = $this->name;
        $waste_type->category = $this->category;
        $waste_type->generation_area = $this->generation_area;
        $waste_type->general_composition = $this->general_composition;
        $waste_type->control_methods = $this->control_methods;
        $waste_type->impact = $this->impact;
        $waste_type->save();

        $this->dispatchBrowserEvent('hide-waste_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Waste Type Created Successfully!!"
        ]);

  
    }

    public function edit($id){
    $waste_type = WasteType::find($id);
  
    $this->name = $waste_type->name;
    $this->category = $waste_type->category;
    $this->general_composition = $waste_type->general_composition;
    $this->generation_area = $waste_type->generation_area;
    $this->impact = $waste_type->impact;
    $this->control_methods = $waste_type->control_methods;
    $this->waste_type_id = $waste_type->id;
    $this->dispatchBrowserEvent('show-waste_typeEditModal');

    }


      public function update(){
     
        
        $this->validate();
        
        $waste_type =  WasteType::find($this->waste_type_id);
        $waste_type->name = $this->name;
        $waste_type->category = $this->category;
        $waste_type->generation_area = $this->generation_area;
        $waste_type->general_composition = $this->general_composition;
        $waste_type->control_methods = $this->control_methods;
        $waste_type->impact = $this->impact;
        $waste_type->update();

        $this->dispatchBrowserEvent('hide-waste_typeEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Waste Type Updated Successfully!!"
        ]);

  
    }

    public function delete($id){
       
        $this->waste_type_id = $id;
        $this->dispatchBrowserEvent('show-waste_typeDeleteModal');
       
    }
    public function destroy(){
        $waste_type = WasteType::find($this->waste_type_id);;
        $waste_type->delete();

        $this->dispatchBrowserEvent('hide-waste_typeDeleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Waste Type Deleted Successfully!!"
        ]);
    }


    public function render()
    {
        
        $query = WasteType::query()->orderBy('name','asc')->paginate(10);

        return view('livewire.waste-types.index',[
            'waste_types' => $query
        ]);
    }
}
