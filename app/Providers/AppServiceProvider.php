<?php

namespace App\Providers;

use App\Helpers\Cart;
use App\Http\Livewire\AccountPoints;
use App\Http\Livewire\Cart\CouponInput;
use App\Http\Livewire\OrderHistory;
use App\Models\Order;
use App\Observers\OrderObserver;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;

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
        View::share('cartCount', Cart::count());

        Fortify::loginView(fn () => view('auth-storefront', ['defaultTab' => 'login']));
        Fortify::registerView(fn () => view('auth-storefront', ['defaultTab' => 'register']));

        Livewire::component('account-points', AccountPoints::class);
        Livewire::component('order-history', OrderHistory::class);
        Livewire::component('cart.coupon-input', CouponInput::class);

        Filament::serving(function () {
            Filament::registerWidgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\MonthlyOrdersChart::class,
                \App\Filament\Widgets\RecentOrders::class,
            ]);
        });

        Order::observe(OrderObserver::class);
    }
}
