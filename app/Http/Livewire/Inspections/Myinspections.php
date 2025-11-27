<?php

namespace App\Http\Livewire\Inspections;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Inspection;
use Livewire\WithPagination;

class Myinspections extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    public $mechanic_id;
    public $employee;
    protected $queryString = ['search'];
    
    public $inspection_results;
    // public $inspections;
    public $inspection_id;



    public function mount($id){
        $this->resetPage();
        $this->employee = Employee::find($id);
        $this->mechanic_id = $id;
    }

     public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getInspectionsProperty(){

            $query = Inspection::query()
                ->with(['booking','horse','service_type','trailer','vehicle'])
                ->whereHas('booking.employees', function ($q) {
                    $q->where('employees.id', $this->mechanic_id);
                })
                // Date range: use given range if both are set, else default to current month
                ->when(filled($this->from) && filled($this->to), function ($q) {
                    $from = Carbon::parse($this->from)->startOfDay();
                    $to   = Carbon::parse($this->to)->endOfDay();
                    $q->whereBetween('created_at', [$from, $to]);
                }, function ($q) {
                    $q->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                })
                // Search block (only when a term is present)
                ->when(filled($this->search), function ($q) {
                    $s = '%'.$this->search.'%';

                    $q->where(function ($inner) use ($s) {
                        $inner->where('inspection_number', 'like', $s)
                            ->orWhereHas('horse', fn ($qq) => $qq
                                ->where('registration_number', 'like', $s)
                                ->orWhere('fleet_number', 'like', $s))
                            ->orWhereHas('vehicle', fn ($qq) => $qq
                                ->where('registration_number', 'like', $s)
                                ->orWhere('fleet_number', 'like', $s))
                            ->orWhereHas('trailer', fn ($qq) => $qq
                                ->where('registration_number', 'like', $s)
                                ->orWhere('fleet_number', 'like', $s))
                            ->orWhereHas('booking', fn ($qq) => $qq
                                ->where('booking_number', 'like', $s))
                            ->orWhereHas('service_type', fn ($qq) => $qq
                                ->where('name', 'like', $s));
                    });
                })
                ->latest('created_at');

            return $query->paginate(10);
    }

    public function render()
    {
         return view('livewire.inspections.myinspections',[
            'inspections' => $this->inspections
          
        ]);
         
    }
}
