<?php

namespace App\Http\Livewire\Racks;

use App\Models\Rack;
use Livewire\Component;
use App\Exports\RacksExport;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $racks;
    public $name;
    public $rack_number;
    public $description;
    public $rack_id;
    public $user_id;

    public function mount(){
        $this->racks = Rack::orderBy('name','asc')->get();
    }
    public function exportRacksCSV(Excel $excel){

        return $excel->download(new RacksExport, 'racks.csv', Excel::CSV);
    }
    public function exportRacksPDF(Excel $excel){

        return $excel->download(new RacksExport, 'racks.pdf', Excel::DOMPDF);
    }
    public function exportRacksExcel(Excel $excel){

        return $excel->download(new RacksExport, 'racks.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->rack_number = "";
        $this->description = "";
    }
    protected $rules = [
        'name' => 'required|unique:racks,name,NULL,id,deleted_at,NULL|string|min:2',
        'rack_number' => 'required|unique:racks,rack_number,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        
        $this->validate();
        
        $rack = new Rack;
        $rack->user_id = Auth::user()->id;
        $rack->name = $this->name;
        $rack->rack_number = $this->rack_number;
        $rack->description = $this->description;
        $rack->save();

        $this->dispatchBrowserEvent('hide-rackModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rack Created Successfully!!"
        ]);

    }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating rack!!"
        ]);
         }
    }

    public function edit($id){
    $rack = Rack::find($id);
  
    $this->user_id = $rack->user_id;
    $this->name = $rack->name;
    $this->rack_id = $rack->id;
    $this->dispatchBrowserEvent('show-rackEditModal');

    }


    public function update()
    {
        if ($this->rack_id) {
            try{

            $rack = Rack::find($this->rack_id);
            $rack->name = $this->name;
            $rack->rack_number = $this->rack_number;
            $rack->description = $this->description;
            $rack->update();

            $this->dispatchBrowserEvent('hide-rackEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Rack Updated Successfully!!"
            ]);
          
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating rack!!"
        ]);
    }

        }
    }


    public function render()
    {
        $this->racks = Rack::all();
        return view('livewire.racks.index',[
            'racks' => $this->racks
        ]);
    }
}
