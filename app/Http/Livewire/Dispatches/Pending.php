<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Dispatch;
use Livewire\WithPagination;
use App\Services\Dispatches\DispatchAuthorizationService;
use App\Services\Dispatches\TyreDispatchFitmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\AuthorizationNotificationMail;
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;

class Pending extends Component
{

    use WithPagination, HasStayOnPageAuthorization;
    protected $paginationTheme = 'bootstrap';
    public $from;
    public $to;
    public $search;
    public bool $notificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false]];
    public $dispatch_filter = "created_at";
    private $dispatches;
    public $dispatch;
    public $dispatch_id;
    public $company;
    public $department;
    public $authorize;
    public $comments;
    public array $fitments = [];      // tyre dispatches: [dispatch_item_id => [fit_type, axle, position]]
    public array $fitUnitOptions = [];

    // Bulk authorization
    public $selectedRows = [];
    public $selectPageRows = false;
    public array $pageIds = [];
    public $bulk_authorize;
    public $bulk_comments;


    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->notificationsOnly = request()->boolean('notifications', false);

    }

    public function authorize($id){
        $dispatch = Dispatch::find($id);
        $this->dispatch_id = $dispatch->id;
        $this->dispatch = $dispatch;
        $this->loadFitments($dispatch);
        $this->dispatchBrowserEvent('show-authorizationModal');
    }

    private function fitmentService(): TyreDispatchFitmentService
    {
        return app(TyreDispatchFitmentService::class);
    }

    private function authorizationService(): DispatchAuthorizationService
    {
        return app(DispatchAuthorizationService::class);
    }

    // Tyre dispatches are approved with a fitting position per tyre; older dispatches
    // without one get it filled in here.
    private function loadFitments(?Dispatch $dispatch): void
    {
        $this->fitments = [];
        $this->fitUnitOptions = [];

        if (!$dispatch || $dispatch->department != "tyre") {
            return;
        }

        $service = $this->fitmentService();
        $units = $service->unitsFor($dispatch);
        foreach ($units as $type => $id) {
            $this->fitUnitOptions[$type] = $service->unitLabel($type, $id) ?? $type;
        }

        foreach ($dispatch->dispatch_items()->with('tyre')->whereNotNull('tyre_id')->get() as $item) {
            $tyre = $item->tyre;
            $this->fitments[$item->id] = [
                'tyre' => $tyre ? trim(($tyre->tyre_number ?? '').($tyre->serial_number ? ' SN#: '.$tyre->serial_number : '')) : '',
                'needs_serial' => $tyre && blank($tyre->serial_number),
                'serial' => '',
                'fit_type' => $item->fit_type ?: (count($units) === 1 ? array_key_first($units) : null),
                'axle' => $item->axle,
                'position' => $item->position,
            ];
        }
    }

    private function notify(Dispatch $dispatch): void
    {
        $user = $dispatch->user;
        if ($user?->email) {
            Mail::to($user->email)->send(new AuthorizationNotificationMail($this->company, "Dispatch Authorization", $user, $dispatch));
        }
    }

    private function listRoute(string $decision): ?string
    {
        $prefix = ['tyre' => 'tyre_dispatches', 'inventory' => 'inventory_dispatches', 'asset' => 'asset_dispatches'][$this->department] ?? null;

        return $prefix ? $prefix.'.'.($decision === 'approved' ? 'approved' : 'rejected') : null;
    }

    public function update()
    {
        $dispatch = Dispatch::find($this->dispatch_id);
        if (!$dispatch) {
            throw new \Exception("Dispatch not found.");
        }

        // Checked up front: approval posts a bill and moves stock, so fail before any of that.
        if ($this->authorize == "approved" && ($message = $this->authorizationService()->tyreProblem($dispatch, $this->fitments))) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => $message
            ]);
            return;
        }

        try {
            $dispatch = $this->authorizationService()->authorize($dispatch, (string) $this->authorize, $this->comments, Auth::id(), $this->fitments);
        } catch (ValidationException $e) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => collect($e->errors())->flatten()->first()]);
            return;
        }

        $this->notify($dispatch);

        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $this->authorize == "approved"
                ? "Dispatch Approved & Item(s) Dispatched Successfully"
                : "Dispatch Rejected Successfully"
        ]);

        if ($route = $this->listRoute((string) $this->authorize)) {
            return $this->redirectOrStay($route);
        }
    }

    // ─── Bulk authorization ──────────────────────────────────────────────────

    public function updatedSelectPageRows($value)
    {
        $this->selectedRows = $value ? array_map('strval', $this->pageIds) : [];
    }

    public function showBulkAuthorize()
    {
        $this->bulk_authorize = null;
        $this->bulk_comments = null;
        $this->resetErrorBag();
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
    }

    /**
     * Each dispatch is authorized on its own (own transaction), so one that can't be
     * approved - e.g. a tyre with no serial, or already out of stock - is skipped with
     * its reason instead of blocking the rest. Tyre fitments come from the dispatch.
     */
    public function bulkAuthorize()
    {
        if (! in_array($this->bulk_authorize, ['approved', 'rejected'], true)) {
            $this->addError('bulk_authorize', 'Please select a decision.');
            return;
        }

        $dispatches = Dispatch::whereIn('id', $this->selectedRows)
            ->where('department', $this->department)
            ->where('authorization', 'pending')
            ->orderBy('id')
            ->get();

        $service = $this->authorizationService();
        $done = 0;
        $skipped = [];
        foreach ($dispatches as $dispatch) {
            $fitments = $service->defaultFitments($dispatch);

            if ($this->bulk_authorize === 'approved' && ($problem = $service->tyreProblem($dispatch, $fitments))) {
                $skipped[] = "{$dispatch->dispatch_number}: {$problem}";
                continue;
            }

            try {
                $dispatch = $service->authorize($dispatch, $this->bulk_authorize, $this->bulk_comments, Auth::id(), $fitments);
                $done++;
            } catch (\Throwable $e) {
                $message = $e instanceof ValidationException ? collect($e->errors())->flatten()->first() : $e->getMessage();
                Log::error("Bulk dispatch authorization failed for {$dispatch->dispatch_number}: ".$e->getMessage());
                $skipped[] = "{$dispatch->dispatch_number}: {$message}";
                continue;
            }

            try {
                $this->notify($dispatch);
            } catch (\Throwable $e) {
                Log::warning("Dispatch {$dispatch->dispatch_number} authorized but its notification failed: ".$e->getMessage());
            }
        }

        $this->reset(['selectedRows', 'selectPageRows']);
        $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');

        $verb = $this->bulk_authorize === 'approved' ? 'approved' : 'rejected';
        if ($skipped) {
            session()->flash('warning', count($skipped).' dispatch(es) not '.$verb.': '.implode(' | ', $skipped));
        }
        $this->dispatchBrowserEvent('alert', [
            'type' => $skipped ? 'warning' : 'success',
            'message' => "{$done} dispatch(es) {$verb}".($skipped ? ', '.count($skipped).' skipped - see the message at the top.' : ' successfully.'),
        ]);

        if (!$skipped && $done > 0 && ($route = $this->listRoute($this->bulk_authorize))) {
            return $this->redirectOrStay($route);
        }
    }

    public function render()
    {
        $base = Dispatch::query()->with(['ticket','horse','vehicle','trailer','employee','department','branch'])
                    ->where('department',$this->department)
                    ->where('authorization','pending');
        if ($this->notificationsOnly) {
            $base->whereYear($this->dispatch_filter, now()->year);
        }else{
            $base->when(filled($this->from) && filled($this->to), function ($q) {
                $q->whereDate($this->dispatch_filter, '>=', $this->from)
                ->whereDate($this->dispatch_filter, '<=', $this->to);
            }, function ($q) {
                // Skip the default "this month" restriction while searching so matches
                // outside the current period (e.g. by product/inventory/tyre/asset #) aren't hidden.
                if (! filled($this->search)) {
                    $q->whereMonth($this->dispatch_filter, Carbon::now()->month)
                    ->whereYear($this->dispatch_filter, Carbon::now()->year);
                }
            });
        }

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
                    ->orWhereHas('dispatch_items.product.brand', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
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

            $this->pageIds = $dispatches->pluck('id')->all();

        return view('livewire.dispatches.pending',[
        'dispatches' => $dispatches
        ]);
    }
}
