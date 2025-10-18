<?php

namespace App\Providers;

use App\Events\SosCreated;
use App\Listeners\NotifySosContacts;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        // Register authorization gates
        Gate::define('view-reports', function ($user) {
            // Allow if user is marked as admin
            if ($user->is_admin) {
                return true;
            }
            
            // Allow if user's email matches ADMIN_EMAIL env variable
            $adminEmail = env('ADMIN_EMAIL');
            if ($adminEmail && $user->email === $adminEmail) {
                return true;
            }
            
            return false;
        });
    }
}
