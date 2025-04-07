<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $horses;
    public $horse_id;
  
    public function mount(){
       
      }

      public function restore($id){
        $this->horse_id = $id;
        $this->dispatchBrowserEvent('show-restoreModal');
    }
    public function update(){
        $horse =  Horse::withTrashed()->find($this->horse_id);
        $horse->archive = 0;
        $horse->status = 1 ;
        $horse->service = 0;
        $horse->update();
        Session::flash('success','Horse Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-restoreModal');
        return redirect()->route('horses.index');
    }


    public function render()
    {
        return view('livewire.horses.archived',[
            'horses' => Horse::where('archive','1')
            ->orderBy('registration_number', 'desc')->paginate(10)
        ]);
    }
}
