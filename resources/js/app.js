import './bootstrap';
import './notifications';

// Listen for Livewire resetAdded event
document.addEventListener('livewire:initialized', () => {
    Livewire.on('resetAdded', () => {
        // This will be handled by the Livewire component itself
        console.log('Reset added state');
    });

    // Also listen for cart updates to refresh the cart count
    Livewire.on('cart-updated', () => {
        console.log('Cart updated event received');
    });
});

