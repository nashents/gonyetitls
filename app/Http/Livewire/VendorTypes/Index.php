<?php

namespace App\Http\Livewire\VendorTypes;

use Livewire\Component;
use App\Models\VendorType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $vendor_types;
    public $name;


    public $vendor_type_id;
    public $user_id;

    public function mount(){
        $this->vendor_types = VendorType::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:vendor_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $vendor_type = new VendorType;
        $vendor_type->user_id = Auth::user()->id;
        $vendor_type->name = $this->name;
        $vendor_type->save();
        $this->dispatchBrowserEvent('hide-vendor_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vendor Type Created Successfully!!"
        ]);
        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('hide-vendor_typeModal');
            $this->dispatchBrowserEvent('alert',[

                'type'=>'error',
                'message'=>"Something went wrong while creating vendor type!!"
            ]);
        }
    }

    public function edit($id){
    $vendor_type = VendorType::find($id);
    $this->updateMode = true;
    $this->user_id = $vendor_type->user_id;
    $this->name = $vendor_type->name;
    $this->vendor_type_id = $vendor_type->id;
    $this->dispatchBrowserEvent('show-vendor_typeEditModal');

    }



    public function update()
    {
        if ($this->vendor_type_id) {
            try{
            $vendor_type = VendorType::find($this->vendor_type_id);
            $vendor_type->update([
                'user_id' => $this->user_id,
                'name' => $this->name,
            ]);
            $this->dispatchBrowserEvent('hide-vendor_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vendor Type Updated Successfully!!"
            ]);

            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('hide-vendor_typeEditModal');
                $this->dispatchBrowserEvent('alert',[

                    'type'=>'error',
                    'message'=>"Something went wrong while updating vendor type!!"
                ]);
            }
        }
    }


    public function render()
    {
        $this->vendor_types = VendorType::latest()->get();
        return view('livewire.vendor-types.index',[
            'vendor_types' => $this->vendor_types
        ]);
    }
}
