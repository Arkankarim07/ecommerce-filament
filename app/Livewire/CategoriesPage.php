<?php

namespace App\Livewire;

use App\Models\Categories;
use Livewire\Component;
use Livewire\Attributes\Title;

class CategoriesPage extends Component
{
    #[Title('Categories Page')] 
    public function render()
    {

        $categories = Categories::where('is_active', true)->get();
        
        return view('livewire.categories-page', [
            'categories' => $categories
        ]);
    }
}
