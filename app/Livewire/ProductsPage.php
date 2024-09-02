<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Categories;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

class ProductsPage extends Component
{
    use LivewireAlert;
    use WithPagination;
    #[Title('Products Page')]
    #[Url()]
    public $selectedCategories = [];

    #[Url()]
    public $selectedBrands = [];
    
    #[Url()]
    public $featured;

    #[Url()]
    public $onSale;
    
    #[Url()]
    public $priceRange = 0;

    #[Url()]
    public $sort = 'latest';

    // add products to cart
    public function addToCart($product_id) {
        $total_count = CartManagement::addItemsToCart($product_id);

        // mengirimkan event bernama 'update-cart-count' dengan data total_count ke komponen Navbar.
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        // menampilkan alert | library github dari https://github.com/jantinnerezo/livewire-alert?tab=readme-ov-file
        $this->alert('success', 'Procuct added to cart success!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
           ]);
    }

    public function render()
    {     
        $products = Product::query()->where('is_active', true);

        if(!empty($this->selectedCategories)) {
            $products->whereIn('category_id', $this->selectedCategories);
        }

        if(!empty($this->selectedBrands)) {
            $products->whereIn('brand_id', $this->selectedBrands);
        }

        if($this->featured) {
            $products->where('is_featured', true);
        }
        if($this->onSale) {
            $products->where('on_sale', true);
        }

        if ($this->priceRange) {
            $products->whereBetween('price', [0, $this->priceRange]);
        }
        if ($this->sort == 'price') {
            $products->orderBy('price');
        }
        if ($this->sort == 'latest') {
            $products->latest();
        }
        

        return view('livewire.products-page', [
            'products' => $products->paginate(4),
            'brands' => Brand::where('is_active', 1)->get(['id', 'name', 'slug']),
            'categories' => Categories::query()->where('is_active', 1)->get(['id', 'name', 'slug']),
        ]);
    }
}
