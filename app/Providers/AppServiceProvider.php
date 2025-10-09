<?php

namespace App\Providers;

use App\Events\SosCreated;
use App\Listeners\NotifySosContacts;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(
            SosCreated::class,
            NotifySosContacts::class,
        );
    }
}
