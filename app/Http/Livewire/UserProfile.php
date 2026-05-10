<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserProfile extends Component
{
    public $name;

    public $email;

    public $phone;

    public $address_line1;

    public $address_line2;

    public $city;

    public $province;

    public $postal_code;

    public $country;

    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:255',
        'address_line1' => 'nullable|string|max:255',
        'address_line2' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'province' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address_line1 = $user->address_line1;
        $this->address_line2 = $user->address_line2;
        $this->city = $user->city;
        $this->province = $user->province;
        $this->postal_code = $user->postal_code;
        $this->country = $user->country;
    }

    public function updateProfile()
    {
        $this->validate();

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ]);

        $this->emit('profileUpdated');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => bcrypt($this->new_password),
        ]);

        // Clear password fields
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->emit('passwordUpdated');
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}
