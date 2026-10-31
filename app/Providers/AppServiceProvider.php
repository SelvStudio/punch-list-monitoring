<?php

namespace App\Providers;

use App\Models\InternalNotification;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFour();

        View::composer('*', function ($view) {
            if (Auth::check()) {
                $userId = Auth::id();
                $unreadCount = InternalNotification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $recentNotifications = InternalNotification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->latest()
                    ->limit(5)
                    ->get();

                $view->with([
                    'navUnreadCount' => $unreadCount,
                    'navNotifications' => $recentNotifications,
                ]);
            }
        });
    }
}
