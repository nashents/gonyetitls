<?php

namespace App\Http\Livewire\ThirdParties;

use App\Models\Trip;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Horse;
use Livewire\Component;
use App\Models\DeliveryNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class Show extends Component
{

    public $trips;
    public $trip;
    public $trip_id;
    public $status;
    public $measurement;
    public $loaded;
    public $loaded_date;
    public $offloaded;
    public $offloaded_date;
    public $payment_status;
    public $selectedStatus = NULL;
    public $selectedDeliveryNote = NULL;

    public $title;
    public $file;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function mount($id){
        $this->trip = Trip::withTrashed()->find($id);
        $this->selectedStatus = $this->trip->status;
    }
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
    public function updated($value){
        $this->validateOnly($value);
    }

    public function updatedSelectedStatus($status)
    {
        if (!is_null($status) ) {
            if ($status == "Offloaded") {
                $this->selectedDeliveryNote = TRUE;
            }else {
                $this->selectedDeliveryNote = NULL;
            }
        }

    }
    protected $messages =[

        'title.*.required' => 'Title field is required',
        'file.*.required' => 'File field is required',


    ];

      protected $rules = [

          'title.0' => 'nullable|string',
          'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
          'title.*' => 'required',
          'file.*' => 'required|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
      ];

    public function status($id){
        $trip = Trip::withTrashed()->find($id);
        $this->trip_id = $trip->id;
        $delivery_note = $trip->delivery_note;
        if ($delivery_note) {
            $this->measurement = $delivery_note->measurement;
            $this->loaded = $delivery_note->loaded;
            $this->loaded_date = $delivery_note->loaded_date;
            $this->offloaded = $delivery_note->offloaded;
            $this->offloaded_date = $delivery_note->offloaded_date;
        }
        $this->dispatchBrowserEvent('show-statusModal');
      }

      public function paymentStatus($id){
        $trip = Trip::withTrashed()->find($id);
        $this->trip_id = $trip->id;
        $this->dispatchBrowserEvent('show-paymentStatusModal');
      }

    public function update(){
        $trip = Trip::withTrashed()->find($this->trip_id);
        $trip->trip_status = $this->selectedStatus;
        $trip->update();
        if (isset($this->loaded)) {
            $delivery_note = $trip->delivery_note;
            if ($delivery_note) {
                $delivery_note->measurement = $this->measurement;
                $delivery_note->loaded = $this->loaded;
                $delivery_note->loaded_date = $this->loaded_date;
                $delivery_note->offloaded = $this->offloaded;
                $delivery_note->offloaded_date = $this->offloaded_date;
                $delivery_note->update();
            }else {
                $delivery_note = new DeliveryNote;
                $delivery_note->user_id = Auth::user()->id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->measurement = $this->measurement;
                $delivery_note->loaded = $this->loaded;
                $delivery_note->loaded_date = $this->loaded_date;
                $delivery_note->offloaded = $this->offloaded;
                $delivery_note->offloaded_date = $this->offloaded_date;
                $delivery_note->save();
            }

        }
        if ($this->selectedStatus == "Offloaded") {
            $horse = Horse::withTrashed()->find($trip->horse_id);
            $horse->status = 1;
            if ($horse->mileage != NULL && $trip->distance != NULL) {
                $horse->mileage = $horse->mileage + $trip->distance;
            }

            $horse->update();

            $driver = Driver::withTrashed()->find($trip->driver_id);
            $driver->status = 1;
            $driver->update();

            if ($trip->trailers->count()>0) {
                foreach ($trip->trailers as $trailer) {
                    $trailer = Trailer::withTrashed()->find($trailer->id);
                    $trailer->status = 1;
                    $trailer->update();
                }
            }
        }
        $this->dispatchBrowserEvent('hide-statusModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Status Updated Successfully!!"
        ]);
        return redirect(route('trips.show', $this->trip_id));

      }

      public function updateStatus(){
        $trip = Trip::withTrashed()->find($this->trip_id);
        $trip->payment_status = $this->payment_status;
        $trip->update();
        $this->dispatchBrowserEvent('hide-paymentStatusModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Payment Status Updated Successfully!!"
        ]);
        return redirect(route('trips.show', $this->trip_id));
      }

    public function render()
    {
        return view('livewire.third-parties.show');
    }
}
