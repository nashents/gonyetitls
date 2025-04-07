<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Query\Builder;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Builder::macro('search', function($field, $string){
            return $string ? $this->where($field, 'like', '%'.$search.'%') : $this;
        });

        Blade::directive('convert', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });
    }
}
