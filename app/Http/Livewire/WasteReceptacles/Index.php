<?php

namespace App\Http\Livewire\WasteReceptacles;

use App\Exports\WasteReceptaclesExport;
use App\Models\WasteReceptacle;
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

    protected $waste_receptacles;
    public $name;
    public $waste_receptacle_id;
    public $description;
    public $status;

    public function mount(){
        
    }
    public function exportwaste_receptaclesCSV(Excel $excel){

        return $excel->download(new WasteReceptaclesExport, 'waste_receptacles.csv', Excel::CSV);
    }
    public function exportwaste_receptaclesPDF(Excel $excel){

        return $excel->download(new WasteReceptaclesExport, 'waste_receptacles.pdf', Excel::DOMPDF);
    }
    public function exportwaste_receptaclesExcel(Excel $excel){

        return $excel->download(new WasteReceptaclesExport, 'waste_receptacles.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->description = "";
    }
    protected $rules = [
        'name' => 'required',
        'description' => 'nullable|string',
    ];

    public function store(){
     
        
        $this->validate();
        
        $waste_receptacle = new WasteReceptacle;
        $waste_receptacle->user_id = Auth::user()->id;
        $waste_receptacle->name = $this->name;
        $waste_receptacle->description = $this->description;
        $waste_receptacle->save();

        $this->dispatchBrowserEvent('hide-waste_receptacleModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Waste Receptacle Created Successfully!!"
        ]);

  
    }

    public function edit($id){
    $waste_receptacle = WasteReceptacle::find($id);
  
    $this->name = $waste_receptacle->name;
    $this->description = $waste_receptacle->description;
    $this->status = $waste_receptacle->status;
    $this->waste_receptacle_id = $waste_receptacle->id;
    $this->dispatchBrowserEvent('show-waste_receptacleEditModal');

    }


      public function update(){
     
        
        $this->validate();
        
        $waste_receptacle =  WasteReceptacle::find($this->waste_receptacle_id);
        $waste_receptacle->name = $this->name;
        $waste_receptacle->description = $this->description;
        $waste_receptacle->status = $this->status;
        $waste_receptacle->update();

        $this->dispatchBrowserEvent('hide-waste_receptacleEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Waste Receptacle Updated Successfully!!"
        ]);

  
    }

    public function delete($id){
       
        $this->waste_receptacle_id = $id;
        $this->dispatchBrowserEvent('show-waste_receptacleDeleteModal');
       
    }
    public function destroy(){
        $waste_receptacle = WasteReceptacle::find($this->waste_receptacle_id);
        $waste_receptacle->delete();

        $this->dispatchBrowserEvent('hide-waste_receptacleDeleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Waste Receptacle Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = WasteReceptacle::query()->orderBy('name','asc')->paginate(10);
        return view('livewire.waste-receptacles.index', [
            'waste_receptacles' => $query,
        ]);
    }
}
