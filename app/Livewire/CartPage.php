<?php

namespace App\Livewire;


use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class CartPage extends Component
{
    use LivewireAlert;
    #[Title('Cart Page')]

    public $cart_items = [];
    public $grand_total;

    public function mount() {
        $this->cart_items = CartManagement::getCartItemsFromCookie();
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
    }


    public function increaseQty($product_id) {
        $this->cart_items = CartManagement::incrementQuantityToCartItem($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        // dd($this->cart_items);
        }
        public function decreaseQty($product_id) {
        $this->cart_items = CartManagement::decrementQuantityToCartItem($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        // dd($this->cart_items);
        }

    public function removeItem($product_id) {
        $this->cart_items = CartManagement::removeCartItem($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);

         // mengirimkan event bernama 'update-cart-count' dengan data total_count ke komponen Navbar.
         $this->dispatch('update-cart-count', total_count: count($this->cart_items))->to(Navbar::class);

         // menampilkan alert | library github dari https://github.com/jantinnerezo/livewire-alert?tab=readme-ov-file
         $this->alert('success', 'Procuct removed from cart!', [
             'position' => 'bottom-end',
             'timer' => 3000,
             'toast' => true,
            ]);
    }
    public function render()
    {
        return view('livewire.cart-page');
    }
}
