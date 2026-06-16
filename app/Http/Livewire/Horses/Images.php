<?php

namespace App\Http\Livewire\Horses;


use App\Models\Horse;
use Livewire\Component;
use App\Models\HorseImage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Images extends Component
{
    use WithFileUploads;


    public $images = [];
    public $horse_images;
    public $image_id;
    public $horse_id;
    public $horse;


    public function mount($id){
        $this->horse_id = $id;
        $this->horse = Horse::find($id);
        $this->horse_images = HorseImage::where('horse_id',$this->horse->id)->latest()->get();
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

                $image = new HorseImage;
                $image->user_id = Auth::user()->id;
                $image->horse_id = $this->horse_id;
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
        $image = HorseImage::find($id);

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
         $this->horse_images = HorseImage::where('horse_id',$this->horse->id)->latest()->get();
        return view('livewire.horses.images',[
            'horse_images' => $this->horse_images
        ]);
    }
}
