<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\SidebarComposer;
use App\Http\View\Composers\NavbarComposer;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
   public function boot(): void
    {
        view()->composer('includes.sidebar', SidebarComposer::class);
        view()->composer('includes.navbar', NavbarComposer::class);
    }
}
