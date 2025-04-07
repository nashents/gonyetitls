<?php

namespace App\Http\Livewire\Transporters;

use Livewire\Component;
use App\Models\Cargo;
use App\Models\Transporter;
use App\Models\TransporterCargo;

class Cargos extends Component
{
    public $transporter;
    public $transporter_id;
    public $transporter_cargos;
    public $transporter_cargo_id;
    public $cargos;
    public $cargo;
    public $cargo_id;
    public $name;
    public $from;
    public $to;

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
        $this->cargo_id = "" ;
    }

    public function mount($id){
        $this->transporter = Transporter::find($id);
        $this->transporter_id = $id;
        $this->cargos = Cargo::latest()->get();
        $this->transporter_cargos =  $this->transporter->cargos;
    }


    public function removeShow($transporter_id, $cargo_id){
        $this->transporter = Transporter::find($transporter_id);
        $this->cargo = Cargo::find($cargo_id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeCargo(){ 
        $this->transporter->cargos()->detach($this->cargo->id);
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cargo(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{
            if (isset($this->cargo_id)) {
                $this->transporter->cargos()->attach($this->cargo_id);
                }
           
            $this->dispatchBrowserEvent('hide-addCargoModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargo(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading cargo(s)!!"
            ]);
        }
    }

    public function render()
    {
        $this->transporter_cargos =  $this->transporter->cargos;
        $this->transporter = Transporter::find( $this->transporter_id);
        
        return view('livewire.transporters.cargos',[
            'transporter_cargos' => $this->transporter_cargos,
            'transporter' => $this->transporter
        ]);
    }
}
