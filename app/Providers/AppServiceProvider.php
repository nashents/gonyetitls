<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use App\Observers\BillObserver;
use App\Observers\CreditNoteObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
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
    }
}
