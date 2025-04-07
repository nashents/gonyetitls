<?php

namespace App\Http\Livewire\Cargos;

use App\Models\Cargo;
use Livewire\Component;
use App\Models\Transporter;

class Transporters extends Component
{

    public $transporters;
    public $transporter_id;
    public $cargo_transporters;
    public $cargo_transporter_id;
    public $cargos;
    public $cargo;
    public $cargo_id;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    private function resetInputFields(){
        $this->transporter_id = "" ;
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount($id){
        $this->cargo = Cargo::find($id);
        $this->cargo_id = $id;
        $this->cargo_transporters = $this->cargo->transporters;
        $this->transporters = Transporter::orderBy('name','asc')->get();
    }

    
    public function removeShow($cargo_id, $transporter_id){
        $this->transporter = Transporter::find($transporter_id);
        $this->cargo = Cargo::find($cargo_id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeTransporter(){ 
        $this->cargo->transporters()->detach($this->transporter->id);
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transporter(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{
            if (isset($this->transporter_id)) {
                $this->cargo->transporters()->attach($this->transporter_id);
                }
           
            $this->dispatchBrowserEvent('hide-addTransporterModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporter(s) Added Successfully!!"
            ]);
            // return redirect(request()->header('Referer'));

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading transporter(s)!!"
            ]);
        }
    }

    public function render()
    {
        
        $this->cargo_transporters = Cargo::find($this->cargo_id)->transporters;
        $this->cargo = Cargo::find( $this->cargo_id);
        
        return view('livewire.cargos.transporters',[
            'cargo_transporters' => $this->cargo_transporters,
            'cargo' => $this->cargo,
        ]);
    }
}
