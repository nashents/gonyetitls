<?php

namespace App\Http\Livewire\Retreads;

use App\Models\Retread;
use Livewire\Component;
use App\Models\RetreadTyre;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    private $retreads;
    public $retread_id;
    public $collection_date;
    public $status;

    public function mount(){
        $this->resetPage();
    }

    public function updated($value){
    $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->collection_date = "";
        $this->status = "";
    }
    protected $rules = [
        'collection_date' => 'required',
        'status' => 'required',
    ];


    public function showRetread($id){
        $this->retread_id = $id;
        $retread = Retread::find($id);
        $this->status = $retread->status;
        $this->dispatchBrowserEvent('show-closeRetreadModal');
    }

    public function closeRetread(){

        $retread = Retread::find($this->retread_id);
        $retread->status = $this->status;
        $retread->collection_date = $this->collection_date;
        $retread->save();
        $retread_tyres = $retread->retread_tyres;
        if ($retread_tyres) {
            foreach ($retread_tyres as $retread_tyre) {
                $tyre = $retread_tyre->tyre;
                if ($tyre) {
                    $tyre->status = 0;
                    $tyre->retread = 0;
                    $tyre->update();
                }
             
            }

        }

        $this->dispatchBrowserEvent('hide-closeRetreadModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Retread Closed Successfully!!"
        ]);

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        return view('livewire.retreads.index',[
            'retreads' => Retread::orderBy('created_at','desc')->paginate(10)
        ]);
    }
}
