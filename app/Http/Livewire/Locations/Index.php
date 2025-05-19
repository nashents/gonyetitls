<?php

namespace App\Http\Livewire\Locations;

use Livewire\Component;
use App\Models\Location;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\LocationsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    private $locations;
    public $location_id;
    public $status;
    public $name;
    public $contact_name;
    public $contact_surname;
    public $email;
    public $phonenumber;
    public $location;
    public $lat;
    public $long;
    public $user_id;
    public $expiry_date;
    public $description;

    protected $listeners = ['setLocationData'];
 
    public function setLocationData($data)
    {
        $this->name = $data['name'];
        $this->lat = $data['lat'];
        $this->long = $data['long'];
        $this->location = $data['location'];
    }

    private function resetInputFields(){
        $this->name = '';
        $this->contact_name = '';
        $this->contact_surname = '';
        $this->email = '';
        $this->phonenumber = '';
        $this->location = '';
        $this->lat = '';
        $this->long = '';
        $this->expiry_date = '';
        $this->description = '';

    }

   

    public function mount(){
        $this->resetPage();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:locations,name,NULL,id,deleted_at,NULL|string|min:2',
        'location' => 'required',
    
    ];

    public function exportLocationsCSV(Excel $excel){

        return $excel->download(new LocationsExport, 'locations.csv', Excel::CSV);
    }
    public function exportLocationsPDF(Excel $excel){

        return $excel->download(new LocationsExport, 'locations.pdf', Excel::DOMPDF);
    }
    public function exportLocationsExcel(Excel $excel){

        return $excel->download(new LocationsExport, 'locations.xlsx');
    }

    

    public function store(){
        // try{

        $location = new Location;
        $location->user_id = Auth::user()->id;
        $location->name = $this->name;
        $location->lat = $this->lat;
        $location->long = $this->long;
        $location->location_pin = $this->location;
        $location->description = $this->description;
        $location->status = '1';
        $location->save();

        $this->dispatchBrowserEvent('hide-locationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Location Created Successfully!!"
        ]);
//     }
//     catch(\Exception $e){
//     $this->dispatchBrowserEvent('hide-locationModal');
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating loading points!!"
//     ]);
// }
    }

    private function expandUrl($url)
    {
        try {
        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get($url);

        return (string) $response->effectiveUri(); // Convert URI object to string
    } catch (\Exception $e) {
        return null;
    }
    }


    public function updatedLocation($location){
  
        
        if (!is_null($location)) {
            if ((!filled($this->lat) && !filled($this->long))) {
            
                if (strpos($location, 'maps.app.goo.gl') !== false) {
                    $fullUrl = $this->expandUrl($location);
                } else {
                    $fullUrl = $location; // Already a long URL
                }

            
                if ($fullUrl) {

               // Case 1: Extract from /search/lat,lng format
               if (preg_match('/\/search\/([-0-9.]+),\+?([-0-9.]+)/', $fullUrl, $matches)) {
               
                    $this->lat = $matches[1];
                    $this->long = $matches[2];
                }
                 // Case 2: Extract from /@lat,lng,zoom/
                elseif (preg_match('/@([-0-9.]+),([-0-9.]+),[0-9.]+z/', $fullUrl, $matches)) {
                   
                    $this->lat = $matches[1];
                    $this->long = $matches[2];
                }
                // Case 3: Extract from !3dlat!4dlong format
                elseif (preg_match('/!3d([-0-9.]+)!4d([-0-9.]+)/', $fullUrl, $matches)) {
                    $this->lat = $matches[1];
                    $this->long = $matches[2];
                }
                else {
                    $this->dispatchBrowserEvent('hide-locationEditModal');
                    $this->dispatchBrowserEvent('hide-locationModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Could not extract coordinates. Please check the URL format."
                    ]);
                }

                } else {
                    $this->dispatchBrowserEvent('hide-locationEditModal');
                    $this->dispatchBrowserEvent('hide-locationModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Invalid or unreachable URL."
                    ]);
                  
                }

            }
    
           
        }
        
      
    }


    public function edit($id){

    $location = Location::find($id);
    $this->user_id = $location->user_id;
    $this->name = $location->name;
    $this->location = $location->location_pin;
    $this->lat = $location->lat;
    $this->long = $location->long;
    $this->status = $location->status;
    $this->description = $location->description;
    $this->location_id = $location->id;

    $this->dispatchBrowserEvent('initializeAutocomplete', [
        'name' => $this->name,
        'lat' => $this->lat,
        'long' => $this->long,
        'location' => $this->location
    ]);

    $this->dispatchBrowserEvent('show-locationEditModal');

    }

    public function update()
    {
        if ($this->location_id) {
            try{
            $location = Location::find($this->location_id);
            $location->user_id = Auth::user()->id;
            $location->name = $this->name;
            $location->location_pin = $this->location;
            $location->lat = $this->lat;
            $location->long = $this->long;
            $location->description = $this->description;
            $location->status = $this->status;
            $location->update();

            $this->dispatchBrowserEvent('hide-locationEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Location Updated Successfully!!"
            ]);

        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-locationEditModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while updating location!!"
        ]);
    }
        }
    }
    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.locations.index',[
                'locations' => Location::where('name','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orWhere('lat','like', '%'.$this->search.'%')
                ->orWhere('long','like', '%'.$this->search.'%')
                ->orderBy('created_at','desc')->paginate(10),
               
            ]);
        }else {
            return view('livewire.locations.index',[
                'locations' => Location::orderBy('created_at','desc')->paginate(10)
            ]);
        }
       
    }
}
