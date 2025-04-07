<?php

namespace App\Http\Livewire\TrailerLinks;

use App\Models\Trailer;
use Livewire\Component;
use App\Models\TrailerLink;
use App\Models\Transporter;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $transporters;
    public $selectedTransporter;
    public $trailers;
    public $trailer_links;
    public $trailer_a;
    public $trailer_b;

    public $trailer_link_id;
    public $user_id;

    public function mount(){
        $this->trailer_links = TrailerLink::latest()->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->trailers = collect();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedTransporter' => 'required',
        'trailer_a' => 'required',
        'trailer_b' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedTransporter = '';
        $this->trailer_b = '';
        $this->trailer_b = '';
    }


    public function updatedSelectedTransporter($transporter){
        if (!is_null($transporter)) {
            $this->trailers = Trailer::where('transporter_id',$transporter)->orderBy('registration_number','asc')->get();
        }
    }

    public function store(){
        try{
        $trailer_link = new TrailerLink;
        $trailer_link->user_id = Auth::user()->id;
        $trailer_link->transporter_id = $this->selectedTransporter;
        $trailer_link->trailer_a = $this->trailer_a;
        $trailer_link->trailer_b = $this->trailer_b;
        $trailer_link->save();

        $this->dispatchBrowserEvent('hide-trailer_linkModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trailer Link Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating trailer link!!"
        ]);
    }
    }

    public function edit($id){
    $trailer_link = TrailerLink::find($id);
    $this->user_id = $trailer_link->user_id;
    $this->selectedTransporter = $trailer_link->transporter_id;
    $this->trailer_a = $trailer_link->trailer_a;
    $this->trailer_b = $trailer_link->trailer_b;
    $this->trailer_link_id = $trailer_link->id;
    $this->dispatchBrowserEvent('show-trailer_linkEditModal');

    }


    public function update()
    {
        if ($this->trailer_link_id) {
            try{
                
            $trailer_link = TrailerLink::find($this->trailer_link_id);
            $trailer_link->user_id = Auth::user()->id;
            $trailer_link->transporter_id = $this->selectedTransporter;
            $trailer_link->trailer_a = $this->trailer_a;
            $trailer_link->trailer_b = $this->trailer_b;
            $trailer_link->update();

            $this->dispatchBrowserEvent('hide-trailer_linkEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"trailer_link Updated Successfully!!"
            ]);


            // return redirect()->trailer_link('trailer_link.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-trailer_linkEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating trailer_link!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->trailer_links = TrailerLink::latest()->get();
        return view('livewire.trailer-links.index',[
            'trailer_links'=>   $this->trailer_links
        ]);
    }
}
