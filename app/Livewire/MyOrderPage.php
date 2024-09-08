<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('My Order')]
class MyOrderPage extends Component
{
    use WithPagination;
    
    public function render()
    {
        $my_order = Order::where('user_id', auth()->id())->latest()->paginate(2);
        return view('livewire.my-order-page', [
            'orders' => $my_order,
        ]);
    }
}
