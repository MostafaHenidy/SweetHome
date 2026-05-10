<?php

namespace App\Http\Livewire;

use App\Models\UserFavorite;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserFavorites extends Component
{
    public $favorites;

    public function mount()
    {
        $this->loadFavorites();
    }

    public function loadFavorites()
    {
        $this->favorites = UserFavorite::with('product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function removeFavorite($favoriteId)
    {
        $favorite = UserFavorite::where('user_id', Auth::id())
            ->where('id', $favoriteId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $this->loadFavorites();
            $this->emit('favoriteRemoved');
        }
    }

    public function toggleFavorite($productId)
    {
        $favorite = UserFavorite::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $this->emit('favoriteRemoved');
        } else {
            UserFavorite::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
            ]);
            $this->emit('favoriteAdded');
        }

        $this->loadFavorites();
    }

    public function render()
    {
        return view('livewire.user-favorites');
    }
}
