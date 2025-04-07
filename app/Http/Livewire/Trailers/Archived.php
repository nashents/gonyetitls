<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Trailer;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $trailers;
    public $trailer_id;
  
    public function mount(){
       
      }

      public function restore($id){
        $this->trailer_id = $id;
        $this->dispatchBrowserEvent('show-restoreModal');
    }
    public function update(){
        $trailer =  Trailer::withTrashed()->find($this->trailer_id);
        $trailer->archive = 0;
        $trailer->status = 1 ;
        $trailer->service = 0;
        $trailer->update();
        Session::flash('success','Trailer Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-restoreModal');
        return redirect()->route('trailers.index');
    }


    public function render()
    {
        return view('livewire.trailers.archived',[
            'trailers' => Trailer::where('archive','1')->orderBy('registration_number', 'desc')->paginate(10)
        ]);
    }
}
