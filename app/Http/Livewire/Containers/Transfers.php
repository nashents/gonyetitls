<?php

namespace App\Http\Livewire\Containers;

use Livewire\Component;
use App\Models\Transfer;
use Livewire\WithPagination;

class Transfers extends Component
{
    private $transfers;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;


    public function mount()
    {
        
    }
    public function render()
    {
        if (filled($this->search)) {
             return view('livewire.containers.transfers',[
                'transfers' => Transfer::query()->where('category', 'fuel')
                ->where('quantity','like', '%'.$this->search.'%')
                ->latest()->paginate(10),
            ]);
        }else{
             return view('livewire.containers.transfers',[
                'transfers' => Transfer::query()->where('category', 'fuel')->latest()->paginate(10),
            ]);
        }
       
    }
}
