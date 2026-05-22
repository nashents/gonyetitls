<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Invoice;
use App\Observers\BillObserver;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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

        Blade::directive('convert', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });

        Bill::observe(BillObserver::class);
        Invoice::observe(InvoiceObserver::class);
    }
}
