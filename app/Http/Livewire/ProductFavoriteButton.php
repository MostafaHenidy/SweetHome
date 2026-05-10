<?php

namespace App\Http\Livewire;

use App\Models\UserFavorite;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductFavoriteButton extends Component
{
    public $productId;

    public $isFavorite = false;

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->updateFavoriteStatus();
    }

    public function updateFavoriteStatus()
    {
        if (Auth::check()) {
            $this->isFavorite = UserFavorite::where('user_id', Auth::id())
                ->where('product_id', $this->productId)
                ->exists();
        } else {
            $this->isFavorite = false;
        }
    }

    public function toggleFavorite()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $favorite = UserFavorite::where('user_id', Auth::id())
            ->where('product_id', $this->productId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $this->isFavorite = false;
            $this->dispatch('notify', message: 'Prodotto rimosso dai preferiti!', type: 'info');
        } else {
            UserFavorite::create([
                'user_id' => Auth::id(),
                'product_id' => $this->productId,
            ]);
            $this->isFavorite = true;
            $this->dispatch('notify', message: 'Prodotto aggiunto ai preferiti!', type: 'success');
        }

        $this->dispatch('favoriteToggled', $this->productId, $this->isFavorite);
    }

    public function render()
    {
        return view('livewire.product-favorite-button');
    }
}
