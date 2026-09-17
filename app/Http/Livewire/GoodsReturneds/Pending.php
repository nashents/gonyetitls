<?php

namespace App\Http\Livewire\GoodsReturneds;

use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use App\Mail\AuthorizationNotificationMail;
use App\Models\GoodsReturned;
use App\Services\GoodsReturneds\GoodsReturnedAuthorizationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{
    use WithPagination, HasStayOnPageAuthorization;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $company;

    public $goods_returned_id;
    public $authorize;
    public $comments;

    public function mount()
    {
        $this->company = Auth::user()->employee->company ?? null;
    }

    public function authorize($id)
    {
        $goodsReturned = GoodsReturned::find($id);
        $this->goods_returned_id = $goodsReturned->id;
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

        $goodsReturned = GoodsReturned::find($this->goods_returned_id);

        if (! $goodsReturned) {
            $this->addError('authorize', 'This return could not be found.');
            return;
        }

        try {
            $goodsReturned = app(GoodsReturnedAuthorizationService::class)->authorize(
                $goodsReturned,
                $this->authorize,
                $this->comments,
                Auth::id()
            );
        } catch (ValidationException $e) {
            $this->addError('authorize', collect($e->errors())->flatten()->first());
            return;
        }

        $user = $goodsReturned->user;
        $email = $user?->email;
        if ($email) {
            Mail::to($email)->send(new AuthorizationNotificationMail($this->company, 'Goods Returned Authorization', $user, $goodsReturned));
        }

        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $this->authorize === 'approved'
                ? 'Return Approved, Stock Adjusted & Debit Note Posted Successfully'
                : 'Return Rejected Successfully',
        ]);

        if ($this->authorize === 'approved') {
            return $this->redirectOrStay('goods_returneds.approved');
        }

        return $this->redirectOrStay('goods_returneds.rejected');
    }

    public function render()
    {
        $base = GoodsReturned::query()
            ->with(['vendor', 'employee', 'user', 'goods_received', 'goods_returned_items'])
            ->where('authorization', 'pending');

        $base->when(filled($this->search), function ($q) {
            $term = '%' . $this->search . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('goods_returned_number', 'like', $term)
                    ->orWhere('return_reference', 'like', $term)
                    ->orWhere('department', 'like', $term)
                    ->orWhereHas('vendor', fn ($sub) => $sub->where('name', 'like', $term))
                    ->orWhereHas('employee', fn ($sub) => $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term));
            });
        });

        return view('livewire.goods-returneds.pending', [
            'goods_returneds' => $base->orderByDesc('created_at')->paginate(10),
        ]);
    }
}
