<?php

namespace App\Providers;

use App\Filament\Widgets\MonthlyOrdersChart;
use App\Filament\Widgets\RecentOrders;
use App\Filament\Widgets\StatsOverview;
use App\Helpers\Cart;
use App\Http\Livewire\AccountPoints;
use App\Http\Livewire\AddToCart;
use App\Http\Livewire\Cart\CouponInput;
use App\Http\Livewire\NotificationPreferences;
use App\Http\Livewire\OrderHistory;
use App\Http\Livewire\ProductFavoriteButton;
use App\Http\Livewire\QuoteHistory;
use App\Http\Livewire\UserDashboard;
use App\Http\Livewire\UserFavorites;
use App\Http\Livewire\UserInquiries;
use App\Http\Livewire\UserProfile;
use App\Models\Order;
use App\Notifications\Channels\FirebaseChannel;
use App\Observers\OrderObserver;
use Filament\Facades\Filament;
use Illuminate\Notifications\ChannelManager;
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
        // Register custom Firebase notification channel
        $this->app->afterResolving(ChannelManager::class, function (ChannelManager $manager) {
            $manager->extend('firebase', function () {
                return $this->app->make(FirebaseChannel::class);
            });
        });
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
        Livewire::component('quote-history', QuoteHistory::class);
        Livewire::component('notification-preferences', NotificationPreferences::class);

        // User management components
        Livewire::component('user-profile', UserProfile::class);
        Livewire::component('user-favorites', UserFavorites::class);
        Livewire::component('user-inquiries', UserInquiries::class);
        Livewire::component('user-dashboard', UserDashboard::class);
        Livewire::component('product-favorite-button', ProductFavoriteButton::class);
        Livewire::component('add-to-cart', AddToCart::class);

        Filament::serving(function () {
            Filament::registerWidgets([
                StatsOverview::class,
                MonthlyOrdersChart::class,
                RecentOrders::class,
            ]);
        });

        Order::observe(OrderObserver::class);
    }
}
