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

    public function mount(){
        $this->resetPage();
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
