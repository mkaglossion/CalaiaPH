<?php

namespace App\View\Components;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ShopProducts extends Component
{
    public $frozenProducts;
    public $freshProducts;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->frozenProducts = Product::where('category', 'frozen')->where('is_active', true)->get();
        $this->freshProducts = Product::where('category', 'fresh')->where('is_active', true)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.shop-products');
    }
}
