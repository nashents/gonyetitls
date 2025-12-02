<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Dispatch;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Rejected extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    protected $queryString = ['search'];
    public $dispatch_filter = "created_at";
    private $dispatches;
    public $dispatch;
    public $dispatch_id;
    public $company;
    public $department;

    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
     
    }


    public function render()
    {
         $base = Dispatch::query()->with(['ticket','horse','vehicle','trailer','employee','department','branch'])
                    ->where('department',$this->department)
                    ->where('authorization','rejected');

            $base->when(filled($this->from) && filled($this->to), function ($q) {
                    $q->whereDate($this->dispatch_filter, '>=', $this->from)
                    ->whereDate($this->dispatch_filter, '<=', $this->to);
                }, function ($q) {
                    $q->whereMonth($this->dispatch_filter, Carbon::now()->month)
                    ->whereYear($this->dispatch_filter, Carbon::now()->year);
                });

               // Search filter (grouped to keep AND/OR logic correct)
            $base->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($qq) use ($term) {
                    $qq->where('dispatch_number', 'like', $term)
                    ->orWhere('authorization', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('ticket', function ($sub) use ($term) {
                        $sub->where('ticket_number', 'like', $term);
                    })
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('store', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    });
                });
            });

            $dispatches = $base
                ->orderByDesc($this->dispatch_filter)
                ->paginate(10);

        return view('livewire.dispatches.rejected',[
        'dispatches' => $dispatches
        ]);
    }
}
