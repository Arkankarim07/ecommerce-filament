<?php

namespace App\Livewire;
use App\Models\Brand;
use App\Models\Categories;
use Livewire\Attributes\Title;
use Livewire\Component;

class HomePage extends Component
{
    #[Title('Home Page')] 
    public function render()
    {

        $brands = Brand::where('is_active', true)->get();

        $category = Categories::where('is_active', true)->get();

        return view('livewire.home-page', [
            'brands' => $brands,
            'category' => $category
        ]);
    }
}
