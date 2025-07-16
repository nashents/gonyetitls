<?php

namespace App\Http\Livewire\Requisitions;

use Livewire\Component;
use App\Models\Requisition;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
{


    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $requisition_filter;

    private $requisitions;
    public $requisition_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $requisition;
    public $company;

    public function mount(){
        $this->company = Auth::user()->employee->company;
        $this->requisition_filter = 'created_at';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
  
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                return view('livewire.requisitions.approved',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->where('authorization','approved')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhereHas('trip', function ($query) {
                        return $query->where('trip_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('requisition_number','desc')->paginate(10),
                    'requisition_filter' => $this->requisition_filter,
                ]);
            }else {
                return view('livewire.requisitions.approved',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->where('authorization','approved')->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy('requisition_number','desc')->paginate(10),
                    'requisition_filter' => $this->requisition_filter,
                ]);
            }
           
        }
        elseif (isset($this->search)) {
           
            return view('livewire.requisitions.approved',[
                'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->where('authorization','approved')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where('requisition_number','like', '%'.$this->search.'%')
                ->orWhere('status','like', '%'.$this->search.'%')
                ->orWhere('date','like', '%'.$this->search.'%')
                ->orWhereHas('trip', function ($query) {
                    return $query->where('trip_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('requisition_number','desc')->paginate(10),
                'requisition_filter' => $this->requisition_filter,
            ]);
        }
        else {
           
            return view('livewire.requisitions.approved',[
                'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->where('authorization','approved')->whereMonth('created_at', date('m'))
                ->whereYear($this->requisition_filter, date('Y'))->orderBy('requisition_number','desc')->paginate(10),
                'requisition_filter' => $this->requisition_filter,
            ]);
          
        }
       
    }
}
