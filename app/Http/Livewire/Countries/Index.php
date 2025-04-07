<?php

namespace App\Http\Livewire\Countries;

use App\Models\Country;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\CountriesExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $countries;
    public $name;

    public $country_id;
    public $user_id;

    public function mount(){
        $this->countries = Country::all();
    }
    public function exportCountriesCSV(Excel $excel){

        return $excel->download(new CountriesExport, 'countries.csv', Excel::CSV);
    }
    public function exportCountriesPDF(Excel $excel){

        return $excel->download(new CountriesExport, 'countries.pdf', Excel::DOMPDF);
    }
    public function exportCountriesExcel(Excel $excel){

        return $excel->download(new CountriesExport, 'countries.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->name = "";
    }
    protected $rules = [
        'name' => 'required|unique:countries,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $country = new Country;
        $country->user_id = Auth::user()->id;
        $country->name = $this->name;
        $country->save();

        $this->dispatchBrowserEvent('hide-countryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Country Created Successfully!!"
        ]);

    }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating country!!"
        ]);
         }
    }

    public function edit($id){
    $country = Country::find($id);
    $this->updateMode = true;
    $this->user_id = $country->user_id;
    $this->name = $country->name;
    $this->country_id = $country->id;
    $this->dispatchBrowserEvent('show-countryEditModal');

    }


    public function update()
    {
        if ($this->country_id) {
            try{
            $country = country::find($this->country_id);
            $country->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
            ]);

            $this->dispatchBrowserEvent('hide-countryEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Country Updated Successfully!!"
            ]);
            // return redirect()->route('countries.index');
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating country!!"
        ]);
    }

        }
    }


    public function render()
    {
        $this->countries = Country::all();
        return view('livewire.countries.index',[
            'countries' => $this->countries
        ]);
    }
}
