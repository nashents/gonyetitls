<?php

namespace App\Http\Livewire\GoodsReceiveds;

use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use App\Mail\AuthorizationNotificationMail;
use App\Models\GoodsReceived;
use App\Services\GoodsReceiveds\GoodsReceivedAuthorizationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination, HasStayOnPageAuthorization;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;
    protected $queryString = ['search'];
    public $goods_received_filter = "created_at";
    public $goods_received;
    public $goods_received_id;
    public $company;
    public $authorize;
    public $comments;

    // Bulk authorization
    public $selectedRows = [];
    public $selectPageRows = false;
    public array $pageIds = [];
    public $bulk_authorize;
    public $bulk_comments;

    public function mount(){
        $this->company = Auth::user()->employee->company;
    }

    public function authorize($id){
        $goods_received = GoodsReceived::find($id);
        $this->goods_received_id = $goods_received->id;
        $this->goods_received = $goods_received;
        $this->authorize = null;
        $this->comments = null;
        $this->dispatchBrowserEvent('show-authorizationModal');
    }

    public function update()
    {
        if (! in_array($this->authorize, ['approved', 'rejected'])) {
            $this->addError('authorize', 'Please select a decision.');
            return;
        }

        $goods_received = GoodsReceived::find($this->goods_received_id);

        if (! $goods_received) {
            $this->addError('authorize', 'This GRV could not be found.');
            return;
        }

        $goods_received = app(GoodsReceivedAuthorizationService::class)->authorize(
            $goods_received,
            $this->authorize,
            $this->comments,
            Auth::id()
        );

        $user = $goods_received->user;
        $email = $user?->email;
        $notification = "Goods Received Authorization";
        if ($email) {
            Mail::to($email)->send(new AuthorizationNotificationMail($this->company, $notification, $user, $goods_received));
        }

        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $this->authorize === 'approved'
                ? "GRV Approved & Item(s) Added to Stock Successfully"
                : "GRV Rejected Successfully",
        ]);

        if ($this->authorize === 'approved') {
            return $this->redirectOrStay('goods_receiveds.approved');
        }

        return $this->redirectOrStay('goods_receiveds.rejected');
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
     * Each GRV goes through GoodsReceivedAuthorizationService in its own transaction
     * (stock release + GL receipt posting), so one failure is skipped with its reason
     * instead of blocking the rest.
     */
    public function bulkAuthorize()
    {
        if (! in_array($this->bulk_authorize, ['approved', 'rejected'], true)) {
            $this->addError('bulk_authorize', 'Please select a decision.');
            return;
        }

        $service = app(GoodsReceivedAuthorizationService::class);
        $grvs = GoodsReceived::whereIn('id', $this->selectedRows)->where('authorization', 'pending')->orderBy('id')->get();

        $done = 0;
        $skipped = [];
        foreach ($grvs as $grv) {
            try {
                $grv = $service->authorize($grv, $this->bulk_authorize, $this->bulk_comments, Auth::id());
                $done++;
            } catch (\Throwable $e) {
                $message = $e instanceof \Illuminate\Validation\ValidationException ? collect($e->errors())->flatten()->first() : $e->getMessage();
                \Illuminate\Support\Facades\Log::error("Bulk GRV authorization failed for {$grv->goods_received_number}: ".$e->getMessage());
                $skipped[] = "{$grv->goods_received_number}: {$message}";
                continue;
            }

            try {
                $user = $grv->user;
                if ($user?->email) {
                    Mail::to($user->email)->send(new AuthorizationNotificationMail($this->company, "Goods Received Authorization", $user, $grv));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("GRV {$grv->goods_received_number} authorized but its notification failed: ".$e->getMessage());
            }
        }

        $this->reset(['selectedRows', 'selectPageRows']);
        $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');

        $verb = $this->bulk_authorize === 'approved' ? 'approved' : 'rejected';
        if ($skipped) {
            session()->flash('warning', count($skipped).' GRV(s) not '.$verb.': '.implode(' | ', $skipped));
        }
        $this->dispatchBrowserEvent('alert', [
            'type' => $skipped ? 'warning' : 'success',
            'message' => "{$done} GRV(s) {$verb}".($verb === 'approved' && $done ? ' & item(s) added to stock' : '')
                .($skipped ? ', '.count($skipped).' skipped - see the message at the top.' : ' successfully.'),
        ]);

        if (!$skipped && $done > 0) {
            return $this->redirectOrStay($verb === 'approved' ? 'goods_receiveds.approved' : 'goods_receiveds.rejected');
        }
    }

    public function render()
    {
        $base = GoodsReceived::query()->with([
                'vendor', 'employee', 'user',
                'inventories' => fn ($q) => $q->withTrashed()->with('product'),
                'tyres' => fn ($q) => $q->withTrashed()->with('product'),
                'assets' => fn ($q) => $q->withTrashed()->with('product'),
            ])
            ->where('authorization', 'pending');

        $base->when(filled($this->from) && filled($this->to), function ($q) {
            $q->whereDate($this->goods_received_filter, '>=', $this->from)
                ->whereDate($this->goods_received_filter, '<=', $this->to);
        }, function ($q) {
            // Skip the default "this month" restriction while searching so matches
            // outside the current period (e.g. by inventory/tyre/asset #) aren't hidden.
            if (! filled($this->search)) {
                $q->whereMonth($this->goods_received_filter, Carbon::now()->month)
                    ->whereYear($this->goods_received_filter, Carbon::now()->year);
            }
        });

        $base->when(filled($this->search), function ($q) {
            $term = '%' . $this->search . '%';

            $q->where(function ($qq) use ($term) {
                $qq->where('goods_received_number', 'like', $term)
                    ->orWhere('department', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('vendor', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    })
                    ->orWhereHas('inventories', function ($sub) use ($term) {
                        // withTrashed: an item can be deleted individually without its
                        // GRV being deleted, and search must still find it there.
                        $sub->withTrashed()
                            ->where('inventory_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    })
                    ->orWhereHas('tyres', function ($sub) use ($term) {
                        $sub->withTrashed()
                            ->where('tyre_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    })
                    ->orWhereHas('assets', function ($sub) use ($term) {
                        $sub->withTrashed()
                            ->where('asset_number', 'like', $term)
                            ->orWhere('serial_number', 'like', $term)
                            ->orWhereHas('product', function ($p) use ($term) {
                                $p->where('name', 'like', $term)
                                    ->orWhere('product_number', 'like', $term)
                                    ->orWhere('identification_number', 'like', $term);
                            });
                    });
            });
        });

        $goods_receiveds = $base
            ->orderByDesc($this->goods_received_filter)
            ->paginate(10);

        $this->pageIds = $goods_receiveds->pluck('id')->all();

        return view('livewire.goods-receiveds.pending', [
            'goods_receiveds' => $goods_receiveds,
        ]);
    }
}
