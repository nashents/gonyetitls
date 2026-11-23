<?php

namespace App\Http\Livewire\OffloadingPoints;

use Livewire\Component;
use App\Models\Consignee;
use Livewire\WithPagination;
use App\Http\Livewire\Concerns\HandlesMapLocation;
use Maatwebsite\Excel\Excel;
use App\Models\OffloadingPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Exports\OffloadingPointsExport;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;
    use HandlesMapLocation;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    private $offloading_points;
    public $offloading_point_id;
    public $consignees;
    public $consignee_id;
    public $status;
    public $name;
    public $contact_name;
    public $contact_surname;
    public $email;
    public $phonenumber;
    public $lat;
    public $location;
    public $long;
    public $user_id;
    public $expiry_date;
    public $description;

    protected $listeners = ['setLocationData'];
 

    private function resetInputFields(){
        $this->name = '';
        $this->lat = '';
        $this->long = '';
        $this->location = '';
        $this->contact_name = '';
        $this->contact_surname = '';
        $this->email = '';
        $this->phonenumber = '';
        $this->expiry_date = '';
        $this->consignee_id = '';
        $this->description = '';
    }
    public function mount(){
        $this->resetPage();
       $this->consignees = Consignee::orderBy('name','desc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    public function exportOffloadingPointsCSV(Excel $excel){

        return $excel->download(new OffloadingPointsExport, 'offloading_points.csv', Excel::CSV);
    }
    public function exportOffloadingPointsPDF(Excel $excel){

        return $excel->download(new OffloadingPointsExport, 'offloading_points.pdf', Excel::DOMPDF);
    }
    public function exportOffloadingPointsExcel(Excel $excel){

        return $excel->download(new OffloadingPointsExport, 'offloading_points.xlsx');
    }

    protected $rules = [
        'lat' => 'nullable|numeric|between:-90,90',
        'long' => 'nullable|numeric|between:-180,180',
        'name' => 'required|unique:offloading_points,name,NULL,id,deleted_at,NULL|string|min:2',
  

    ];

    public function store(){
        try{
        $offloading_point = new OffloadingPoint;
        $offloading_point->user_id = Auth::user()->id;
        $offloading_point->name = $this->name;
        $offloading_point->contact_name = $this->contact_name;
        $offloading_point->consignee_id = $this->consignee_id;
        $offloading_point->contact_surname = $this->contact_surname;
        $offloading_point->email = $this->email;
        $offloading_point->phonenumber = $this->phonenumber;
        $offloading_point->lat = $this->lat;
        $offloading_point->long = $this->long;
        $offloading_point->location = $this->location;
        $offloading_point->expiry_date = $this->expiry_date;
        $offloading_point->description = $this->description;
        $offloading_point->status = '1';
        $offloading_point->save();

        $this->dispatchBrowserEvent('hide-offloadingPointModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Offloading Point Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-offloadingPointModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating offloading points!!"
        ]);
    }
    }




    public function edit($id){
    $offloading_point = OffloadingPoint::find($id);
    $this->user_id = $offloading_point->user_id;
    $this->name = $offloading_point->name;
    $this->contact_name = $offloading_point->contact_name;
    $this->contact_surname = $offloading_point->contact_surname;
    $this->phonenumber = $offloading_point->phonenumber;
    $this->consignee_id = $offloading_point->consignee_id;
    $this->email = $offloading_point->email;
    $this->lat = $offloading_point->lat;
    $this->long = $offloading_point->long;
    $this->location = $offloading_point->location;
    $this->status = $offloading_point->status;
    $this->expiry_date = $offloading_point->expiry_date;
    $this->description = $offloading_point->description;
    $this->offloading_point_id = $offloading_point->id;

    $this->dispatchBrowserEvent('initializeAutocomplete', [
        'name' => $this->name,
        'lat' => $this->lat,
        'long' => $this->long,
        'location' => $this->location
    ]);

    
    $this->dispatchBrowserEvent('show-offloadingPointEditModal');

    }

    public function update()
    {

        
        if ($this->offloading_point_id) {
            try{
            $offloading_point = OffloadingPoint::find($this->offloading_point_id);
            $offloading_point->user_id = Auth::user()->id;
            $offloading_point->name = $this->name;
            $offloading_point->contact_name = $this->contact_name;
            $offloading_point->contact_surname = $this->contact_surname;
            $offloading_point->phonenumber = $this->phonenumber;
            $offloading_point->consignee_id = $this->consignee_id;
            $offloading_point->email = $this->email;
            $offloading_point->location = $this->location;
            $offloading_point->lat = $this->lat;
            $offloading_point->long = $this->long;
            $offloading_point->expiry_date = $this->expiry_date;
            $offloading_point->description = $this->description;
            $offloading_point->status = $this->status;
            $offloading_point->update();

            $this->dispatchBrowserEvent('hide-offloadingPointEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Offloading Point Updated Successfully!!"
            ]);

            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-offloadingPointEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating offloading points!!"
            ]);
        }
        }
    }
    public function render()
    {
        $this->consignees = Consignee::orderBy('name','desc')->get();

        if (isset($this->search)) {
            return view('livewire.offloading-points.index',[
                'offloading_points' => OffloadingPoint::where('name','like', '%'.$this->search.'%')
                ->orWhere('lat','like', '%'.$this->search.'%')
                ->orWhere('long','like', '%'.$this->search.'%')
                ->orWhere('contact_name','like', '%'.$this->search.'%')
                ->orWhere('expiry_date','like', '%'.$this->search.'%')
                ->orWhere('contact_surname','like', '%'.$this->search.'%')
                ->orWhere('email','like', '%'.$this->search.'%')
                ->orWhere('phonenumber','like', '%'.$this->search.'%')
                ->orderBy('created_at','desc')->paginate(10),
                'consignees' => $this->consignees
               
            ]);
        }else {
            return view('livewire.offloading-points.index',[
                'offloading_points' => OffloadingPoint::orderBy('created_at','desc')->paginate(10),
                'consignees' => $this->consignees
            ]);
        }
    }
}
