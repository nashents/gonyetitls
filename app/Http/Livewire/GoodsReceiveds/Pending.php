<?php

namespace App\Http\Livewire\GoodsReceiveds;

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
    use WithPagination;

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

        $goods_received = GoodsReceived::findOrFail($this->goods_received_id);

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

        $this->authorize = null;
        $this->comments = null;
    }

    public function render()
    {
        $base = GoodsReceived::query()->with(['vendor', 'employee', 'user'])
            ->where('authorization', 'pending');

        $base->when(filled($this->from) && filled($this->to), function ($q) {
            $q->whereDate($this->goods_received_filter, '>=', $this->from)
                ->whereDate($this->goods_received_filter, '<=', $this->to);
        }, function ($q) {
            $q->whereMonth($this->goods_received_filter, Carbon::now()->month)
                ->whereYear($this->goods_received_filter, Carbon::now()->year);
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
                    });
            });
        });

        $goods_receiveds = $base
            ->orderByDesc($this->goods_received_filter)
            ->paginate(10);

        return view('livewire.goods-receiveds.pending', [
            'goods_receiveds' => $goods_receiveds,
        ]);
    }
}
