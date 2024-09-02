<?php

namespace App\Livewire\Partials;

use App\Helpers\CartManagement;
use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{
    public $total_count = 0;

    // method ini akan dipanggil ketika komponen di-render
    public function mount() {
        $this->total_count = count(CartManagement::getCartItemsFromCookie());
    }

    //Saat event 'update-cart-count' dikirim dengan data total_count, method ini akan dijalankan dan memperbarui nilai total_count pada komponen.
    #[On('update-cart-count')]
    public function updateCartCount($total_count) {
        $this->total_count = $total_count;
    }
    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
