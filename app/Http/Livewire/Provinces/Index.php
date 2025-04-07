<?php

namespace App\Http\Livewire\Provinces;

use App\Models\Country;
use Livewire\Component;
use App\Models\Province;
use Maatwebsite\Excel\Excel;
use App\Exports\ProvincesExport;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $provinces;
    public $countries;
    public $country_id;
    public $name;
    public $province_id;
    public $user_id;

    public function mount(){
        $this->provinces = Province::with('country')->orderBy('name','asc')->get()->sortBy('country.name');
        $this->countries = Country::orderBy('name','asc')->get();
    }
    public function exportprovincesCSV(Excel $excel){

        return $excel->download(new ProvincesExport, 'provinces.csv', Excel::CSV);
    }
    public function exportprovincesPDF(Excel $excel){

        return $excel->download(new ProvincesExport, 'provinces.pdf', Excel::DOMPDF);
    }
    public function exportprovincesExcel(Excel $excel){

        return $excel->download(new ProvincesExport, 'provinces.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
        $this->country_id = "";
    }
    protected $rules = [
        'country_id' => 'required',
        'name' => 'required|unique:provinces,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
            $province = new Province;
            $province->user_id = Auth::user()->id;
            $province->country_id = $this->country_id;
            $province->name = $this->name;
            $province->save();

        $this->dispatchBrowserEvent('hide-provinceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"province Created Successfully!!"
        ]);

    }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating province!!"
        ]);
         }
    }

    public function edit($id){
    $province = Province::find($id);
    $this->user_id = $province->user_id;
    $this->name = $province->name;
    $this->country_id = $province->country_id;
    $this->province_id = $province->id;
    $this->dispatchBrowserEvent('show-provinceEditModal');

    }


    public function update()
    {
        if ($this->province_id) {
            try{
            $province = Province::find($this->province_id);
            $province->user_id = Auth::user()->id;
            $province->country_id = $this->country_id;
            $province->name = $this->name;
            $province->update();

            $this->dispatchBrowserEvent('hide-provinceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Province Updated Successfully!!"
            ]);
            // return redirect()->route('provinces.index');
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating province!!"
        ]);
    }

        }
    }


    public function render()
    {
        $this->provinces = Province::all();
        return view('livewire.provinces.index',[
            'provinces' => $this->provinces
        ]);
    }
}
