<?php

namespace App\Http\Livewire\Disposes;

use App\Models\Dispose;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $disposes;
    public $dispose_id;

    public function mount(){
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function render()
    {
        return view('livewire.disposes.index',[
            'disposes' => Dispose::orderBy('created_at','desc')->paginate(10)
        ]);
    }
}
