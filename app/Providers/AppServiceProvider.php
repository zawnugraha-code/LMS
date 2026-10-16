<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        Model::preventLazyLoading(! app()->isProduction());

        // Global Layout Notifications & User Preferences View Composer
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $notifService = app(NotificationService::class);
                $view->with('unreadNotificationsCount', $notifService->getUnreadCount($user));
                $view->with('recentNotifications', $notifService->getRecentNotifications($user, 10));
            }
        });
    }
}
