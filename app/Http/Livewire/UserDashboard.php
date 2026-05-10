<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\UserFavorite;
use App\Models\UserInquiry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserDashboard extends Component
{
    public $recentOrders;

    public $favoriteCount;

    public $pendingInquiries;

    public $totalPoints;

    public function mount()
    {
        $user = Auth::user();

        $this->recentOrders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->favoriteCount = UserFavorite::where('user_id', $user->id)->count();
        $this->pendingInquiries = UserInquiry::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $this->totalPoints = $user->points_balance ?? 0;
    }

    public function render()
    {
        return view('livewire.user-dashboard');
    }
}
