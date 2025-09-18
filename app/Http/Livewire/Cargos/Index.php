<?php

namespace App\Http\Livewire\Cargos;

use App\Models\Cargo;
use Livewire\Component;
use App\Models\Measurement;
use App\Models\Transporter;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\CargosExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $cargos;
    public $type;
    public $group;
    public $risk;
    public $name;
    public $sku;
    public $measurement;
    public $measurements;
 

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
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'measurement' => 'required',
        'name' => 'required|unique:cargos,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->type = '';
        $this->group = '';
        $this->name = '';
        $this->sku = '';
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
    
        DB::transaction(function () {
        $cargo = new Cargo;
        $cargo->user_id = Auth::user()->id;
        $cargo->name = $this->name;
        $cargo->sku = $this->sku;
        $cargo->measurement = $this->measurement;
        $cargo->group = $this->group;
        $cargo->type = $this->type;
        $cargo->risk = $this->risk;
        $cargo->save();

        if (!empty($this->transporter_id) && is_numeric($this->transporter_id)) {
            // Check if it's not already attached
            if (!$cargo->transporters->contains($this->transporter_id)) {
                $cargo->transporters()->sync($this->transporter_id);
            }
        }
        $this->dispatchBrowserEvent('hide-cargoModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Cargo Created Successfully!!"
        ]);
    });

       
    }

    public function edit($id){

    $cargo = Cargo::find($id);

    $this->user_id = $cargo->user_id;
    $this->name = $cargo->name;
    $this->type = $cargo->type;
    $this->sku = $cargo->sku;
    $this->measurement = $cargo->measurement;
    $this->group = $cargo->group;
    $this->risk = $cargo->risk;
    $this->cargo_id = $cargo->id;
    $this->dispatchBrowserEvent('show-cargoEditModal');

    }


    public function update()
    {
          DB::transaction(function () {

        if ($this->cargo_id) {
         
            $cargo = Cargo::find($this->cargo_id);
            $cargo->name = $this->name;
            $cargo->measurement = $this->measurement;
            $cargo->group = $this->group;
            $cargo->sku = $this->sku;
            $cargo->type = $this->type;
            $cargo->risk = $this->risk;
            $cargo->update();

            $this->dispatchBrowserEvent('hide-cargoEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargo Updated Successfully!!"
            ]);

        }
    });
    }


    public function render()
    {
        return view('livewire.cargos.index',[
            'cargos'=> Cargo::orderBy('name','asc')->paginate(10)
        ]);
    }
}
