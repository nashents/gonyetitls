<?php

namespace App\Http\Livewire\trailers;


use App\Models\Trailer;
use Livewire\Component;
use App\Models\TrailerImage;
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

    protected $rules = [
        'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
    ];


    public function store(){

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
   
    }

    public function deleteImage($id)
    {
        $image = TrailerImage::find($id);

        if (!$image) {
            return;
        }

        $path = public_path('images/uploads/' . $image->filename);

        if (file_exists($path)) {
            unlink($path);
        }

        $image->delete();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Image deleted successfully.'
        ]);
    }

    public function render()
    {
         $this->trailer_images = TrailerImage::where('trailer_id',$this->trailer->id)->latest()->get();
        return view('livewire.trailers.images',[
            'trailer_images' => $this->trailer_images
        ]);
    }
}
