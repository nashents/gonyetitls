<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Trailer;
use Livewire\Component;
use App\Models\TrailerImage;
use App\Models\VehicleImage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Images extends Component
{
    use WithFileUploads;
    public $images = [];
    public $trailer_images;
    public $image_id;
    public $trailer_id;
    public $trailer;


    public function mount($id){
        $this->trailer_id = $id;
        $this->trailer = Trailer::find($id);
        $this->trailer_images = TrailerImage::where('trailer_id',$this->trailer->id)->latest()->get();
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

                $image = new TrailerImage;
                $image->user_id = Auth::user()->id;
                $image->trailer_id = $this->trailer_id;
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
         $this->trailer_images = TrailerImage::where('trailer_id',$this->trailer->id)->latest()->get();
        return view('livewire.trailers.images',[
            'trailer_images' => $this->trailer_images
        ]);
    }
}
