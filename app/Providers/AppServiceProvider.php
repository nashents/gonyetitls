<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Booking;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\DispatchItem;
use App\Models\Fuel;
use App\Models\GoodsReceived;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Ticket;
use App\Models\Trip;
use App\Observers\BillObserver;
use App\Observers\BookingObserver;
use App\Observers\CreditNoteObserver;
use App\Observers\DebitNoteObserver;
use App\Observers\DispatchItemObserver;
use App\Observers\FuelObserver;
use App\Observers\GoodsReceivedObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\PurchaseObserver;
use App\Observers\TicketObserver;
use App\Observers\TripObserver;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Component as LivewireComponent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
       
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        
        Builder::macro('search', function($field, $string){
            return $string ? $this->where($field, 'like', '%'.$string.'%') : $this;
        });

        // Livewire\Component (v2) doesn't ship an authorize() helper the way
        // Illuminate\Routing\Controller does. Several Livewire components in
        // this app call $this->authorize(...) expecting policy-based
        // authorization — without this macro that call throws
        // BadMethodCallException before the policy check ever runs.
        LivewireComponent::macro('authorize', function ($ability, $arguments = []) {
            return Gate::authorize($ability, $arguments);
        });

        Blade::directive('convert', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });

        Bill::observe(BillObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
        CreditNote::observe(CreditNoteObserver::class);
        DebitNote::observe(DebitNoteObserver::class);
        Fuel::observe(FuelObserver::class);
        Purchase::observe(PurchaseObserver::class);
        GoodsReceived::observe(GoodsReceivedObserver::class);
        Booking::observe(BookingObserver::class);
        Ticket::observe(TicketObserver::class);
        DispatchItem::observe(DispatchItemObserver::class);
        Trip::observe(TripObserver::class);
    }
}
