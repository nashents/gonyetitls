<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Dispatch;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Dispatches\DispatchReversalService;

class Approved extends Component
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
    public $reversal_comments;

    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;

    }

    public function reverse($id){
        $dispatch = Dispatch::find($id);
        $this->dispatch_id = $dispatch->id;
        $this->dispatch = $dispatch;
        $this->reversal_comments = null;
        $this->dispatchBrowserEvent('show-reverseModal');
    }

    public function confirmReverse(){

        $this->validate([
            'reversal_comments' => 'required|string|min:3',
        ], [
            'reversal_comments.required' => 'Please explain why this dispatch is being reversed.',
        ]);

        $dispatch = Dispatch::findOrFail($this->dispatch_id);

        try {
            app(DispatchReversalService::class)->reverse($dispatch, $this->reversal_comments, Auth::id());
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
            return;
        }

        $this->reversal_comments = null;
        $this->dispatchBrowserEvent('hide-reverseModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Dispatch reversed successfully — moved to Rejected.',
        ]);
    }


    public function render()
    {
           $base = Dispatch::query()->with(['ticket','horse','vehicle','trailer','employee','department','branch'])
                    ->where('department',$this->department)
                    ->where('authorization','approved');

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
                    })
                    ->orWhereHas('dispatch_items.product', function ($sub) use ($term) {
                        $sub->where('product_number', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('identification_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.inventory', function ($sub) use ($term) {
                        $sub->where('inventory_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.tyre', function ($sub) use ($term) {
                        $sub->where('tyre_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    })
                    ->orWhereHas('dispatch_items.asset', function ($sub) use ($term) {
                        $sub->where('asset_number', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                    });
                });
            });

            $dispatches = $base
                ->orderByDesc($this->dispatch_filter)
                ->paginate(10);

        return view('livewire.dispatches.approved',[
        'dispatches' => $dispatches
        ]);
    }
}
