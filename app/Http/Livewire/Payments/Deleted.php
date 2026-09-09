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
        return view('livewire.payments.deleted', [
            'payments' => Payment::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10),
        ]);
    }
}
