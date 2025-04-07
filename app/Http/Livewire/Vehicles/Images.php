<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\VehicleImage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Images extends Component
{
    use WithFileUploads;
    public $images = [];
    public $vehicle_images;
    public $image_id;
    public $vehicle_id;
    public $vehicle;


    public function mount($id){
        $this->vehicle_id = $id;
        $this->vehicle = Vehicle::find($id);
        $this->vehicle_images = VehicleImage::where('vehicle_id',$this->vehicle->id)->latest()->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[

      'title.*.required' => 'You need to upload at least one image',
      'title.*.image' => 'Your file needs to be an image',

  ];
    protected $rules = [
        'images.*' => 'required|image',

    ];


    public function store(){
        // try{
        if (isset($this->images)) {

            foreach ($this->images as $image) {
                // get file with ext
                $fileNameWithExt = $image->getClientOriginalName();
                //get filename
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //get extention
                $extention = $image->getClientOriginalExtension();
                //file name to store
                $fileNameToStore= $filename.'_'.time().'.'.$extention;
                $image->storeAs('/uploads', $fileNameToStore, 'path');

                $image = new VehicleImage;
                $image->user_id = Auth::user()->id;
                $image->vehicle_id = $this->vehicle_id;
                $image->filename = $fileNameToStore;
                $image->save();
            }
            $this->dispatchBrowserEvent('hide-imageModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Image(s) Uploaded Successfully!!"
            ]);
        }
    // }catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something went wrong while uploading image(s)!!"
    //     ]);
    // }
    }

    public function render()
    {
         $this->vehicle_images = VehicleImage::where('vehicle_id',$this->vehicle->id)->latest()->get();
        return view('livewire.vehicles.images',[
            'vehicle_images' => $this->vehicle_images
        ]);
    }
}
