<?php

namespace App\Providers;

use App\Services\NotificationService;
use Carbon\Carbon;
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
        require_once app_path('Helpers/NotificationHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        // Support for APP_FAKE_NOW (Carbon::setTestNow) for local demo dates
        if (! app()->isProduction() && env('APP_FAKE_NOW')) {
            Carbon::setTestNow(Carbon::parse(env('APP_FAKE_NOW')));
        }

        // Global Layout Notifications & User Preferences View Composer
        View::composer(['layouts.app', 'layouts.dosen'], function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $notifService = app(NotificationService::class);
                $view->with('unreadNotificationsCount', $notifService->getUnreadCount($user));
                $view->with('recentNotifications', $notifService->getRecentNotifications($user, 10));
            }
        });
    }
}
