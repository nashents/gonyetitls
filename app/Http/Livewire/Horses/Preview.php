<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{


    public $selectedFilter;
    public $from;
    public $to;
    public $company;
    protected $horses;
    public $horse;
    public $results;
    public $result;

    public function mount($selectedFilter,  $from, $to){
        $this->selectedFilter = $selectedFilter;
        $this->from = $from;
        $this->to = $to;
        $this->company = Auth::user()->employee->company;

       if ( isset($this->selectedFilter)) {

            if (isset($this->from) && isset($this->to)) {
                $this->horses = Horse::whereBetween('created_at',[$this->from, $this->to] )->get();
            }else {
                $this->horses = Horse::all();
            }
          
        }

    }


    public function render()
    {
        if ( isset($this->selectedFilter)) {

            if (isset($this->from) && isset($this->to)) {
                $this->horses = Horse::whereBetween('created_at',[$this->from, $this->to] )->get();
            }else {
                $this->horses = Horse::all();
            }
          
        }
        return view('livewire.horses.preview',[
            'horses' => $this->horses,
        ]);
    }
}
