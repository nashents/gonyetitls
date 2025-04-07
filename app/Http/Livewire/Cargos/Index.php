<?php

namespace App\Http\Livewire\Cargos;

use App\Models\Cargo;
use Livewire\Component;
use App\Models\Transporter;
use Maatwebsite\Excel\Excel;
use App\Exports\CargosExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $cargos;
    public $type;
    public $group;
    public $risk;
    public $name;
    public $measurement;
 

    public $cargo_id;
    public $user_id;

    public $transporters;
    public $transporter_id;
    public $cargo_transporters;
    public $cargo_transporter_id;
   
    public $cargo;
  

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

    public function mount(){
        $this->cargos = Cargo::all();
        $this->transporters = Transporter::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'group' => 'required',
        'risk' => 'required',
        'measurement' => 'required',
        'name' => 'required|unique:cargos,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->type = '';
        $this->group = '';
        $this->name = '';
        $this->risk = '';
        $this->chargeable_loss = '';
        $this->measurement = '';
        $this->transporter_id = "" ;
    }

    public function exportCargosCSV(Excel $excel){

        return $excel->download(new CargosExport, 'cargos.csv', Excel::CSV);
    }
    public function exportCargosPDF(Excel $excel){

        return $excel->download(new CargosExport, 'cargos.pdf', Excel::DOMPDF);
    }
    public function exportCargosExcel(Excel $excel){
        return $excel->download(new CargosExport, 'cargos.xlsx');
    }

    public function store(){
        // try{
        $cargo = new Cargo;
        $cargo->user_id = Auth::user()->id;
        $cargo->name = $this->name;
        $cargo->measurement = $this->measurement;
        $cargo->group = $this->group;
        $cargo->type = $this->type;
        $cargo->risk = $this->risk;
        $cargo->save();

        if (isset($this->transporter_id)) {
            $cargo->transporters()->attach($this->transporter_id);
            }

        $this->dispatchBrowserEvent('hide-cargoModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cargo Created Successfully!!"
        ]);

        // return redirect()->route('cargos.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating cargo!!"
    //     ]);
    // }
    }

    public function edit($id){
    $cargo = Cargo::find($id);

    $this->user_id = $cargo->user_id;
    $this->name = $cargo->name;
    $this->type = $cargo->type;
    $this->measurement = $cargo->measurement;
    $this->group = $cargo->group;
    $this->risk = $cargo->risk;
    $this->cargo_id = $cargo->id;
    $this->dispatchBrowserEvent('show-cargoEditModal');

    }


    public function update()
    {
        if ($this->cargo_id) {
            try{
            $cargo = Cargo::find($this->cargo_id);
            $cargo->name = $this->name;
            $cargo->measurement = $this->measurement;
            $cargo->group = $this->group;
            $cargo->type = $this->type;
            $cargo->risk = $this->risk;
            $cargo->update();

            $this->dispatchBrowserEvent('hide-cargoEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargo Updated Successfully!!"
            ]);


            // return redirect()->route('cargos.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-cargoEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating cargo!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->cargos = Cargo::latest()->get();
        return view('livewire.cargos.index',[
            'cargos'=>   $this->cargos
        ]);
    }
}
