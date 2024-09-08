<?php

namespace App\Livewire;

use App\Models\Addresses;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('My Order Detail')]
class MyOrderDetailPage extends Component
{
public $order_id;


public function mount($order_id) {
    $this->$order_id = $order_id;
}

    public function render()
    {
        $order_items = OrderItem::where('order_id', $this->order_id)->get();
        $address = Addresses::where('order_id', $this->order_id)->first();
        $order = Order::where('id', $this->order_id)->first();
        return view('livewire.my-order-detail-page', [
            'order_items' => $order_items,
            'address' => $address,
            'order' => $order
        ]);
    }
}
