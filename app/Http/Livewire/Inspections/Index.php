<?php

namespace App\Http\Livewire\Inspections;

use Livewire\Component;
use App\Models\Inspection;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    protected $queryString = ['search'];
    
    public $inspection_results;
    // public $inspections;
    public $inspection_id;



    public function mount(){
        $this->resetPage();
    }

     public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getInspectionsProperty(){

             if (filled($this->from) && filled($this->to)) {

                if (filled($this->search)) {

                    return Inspection::query()->with('booking','horse','service_type','trailer','vehicle')
                    ->whereBetween('created_at',[$this->from, $this->to] )
                    ->where(function ($query) {
                          $query->where('inspection_number','like', '%'.$this->search.'%')
                                ->orWhereHas('horse', function ($query) {
                                    return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('service_type', function ($query) {
                                    return $query->where('name', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('booking', function ($query) {
                                    return $query->where('booking_number', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('vehicle', function ($query) {
                                    return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                })
                              
                                ->orWhereHas('trailer', function ($query) {
                                    return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                });
                        })->orderBy('created_at','desc')->paginate(10);
                }else {
                    return Inspection::query()->with('booking','horse','service_type','trailer','vehicle')
                    ->whereBetween('created_at',[$this->from, $this->to] )
                    ->orderBy('created_at','desc')->paginate(10);
                }
               
            }
            elseif (filled($this->search)) {
               
                return Inspection::query()->with('booking','horse','service_type','trailer','vehicle')
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where(function ($query) {
                          $query->where('inspection_number','like', '%'.$this->search.'%')
                                ->orWhereHas('horse', function ($query) {
                                    return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('service_type', function ($query) {
                                    return $query->where('name', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('booking', function ($query) {
                                    return $query->where('booking_number', 'like', '%'.$this->search.'%');
                                })
                                ->orWhereHas('vehicle', function ($query) {
                                    return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                })
                              
                                ->orWhereHas('trailer', function ($query) {
                                    return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                                ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                                });
                        })->orderBy('created_at','desc')->paginate(10);
            }
            else {
               
                return Inspection::query()->with('booking','horse','service_type','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->paginate(10);
              
            }
    }

    public function render()
    {
         return view('livewire.inspections.index',[
            'inspections' => $this->inspections
          
        ]);
         
    }
}
