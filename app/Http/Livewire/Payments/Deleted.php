<?php

namespace App\Http\Livewire\Payments;

use App\Models\Payment;
use App\Services\Accounting\PaymentRestorationService;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $payment_id;

    public function restore($id)
    {
        $this->payment_id = $id;
        $this->dispatchBrowserEvent('show-paymentRestoreModal');
    }

    public function update()
    {
        $payment = Payment::withTrashed()->findOrFail($this->payment_id);

        try {
            app(PaymentRestorationService::class)->restore($payment);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('hide-paymentRestoreModal');
            $this->dispatchBrowserEvent('alert', [
                'type' => 'danger',
                'message' => $e->getMessage(),
            ]);
            return;
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Payment Restored Successfully!! Its account/wallet balances and journal entries have been reapplied.',
        ]);
        $this->dispatchBrowserEvent('hide-paymentRestoreModal');

        return redirect()->route('payments.index');
    }

    public function render()
    {
        $query = Payment::onlyTrashed()->with(['customer', 'vendor', 'currency', 'user', 'deleted_by']);

        $search = trim((string) ($this->search ?? ''));
        if ($search !== '') {
            $term = "%{$search}%";
            $query->where(function ($q) use ($term) {
                $q->where('payment_number', 'like', $term)
                    ->orWhere('transaction_category', 'like', $term)
                    ->orWhere('mode_of_payment', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('customer', fn ($qq) => $qq->where('name', 'like', $term))
                    ->orWhereHas('vendor', fn ($qq) => $qq->where('name', 'like', $term))
                    ->orWhereHas('currency', fn ($qq) => $qq->where('name', 'like', $term));
            });
        }

        return view('livewire.payments.deleted', [
            'payments' => $query->orderBy('deleted_at', 'desc')->paginate(10),
        ]);
    }
}
