<?php

namespace App\Livewire;

use App\Helpers\CartMangement;
use App\Livewire\Partials\Navbar;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cart')]
class CartPage extends Component
{
    public $cart_items = [];

    public $grand_total;

    public function mount(){
        $this->cart_items = CartMangement::getCartItemsFromCookie();
        $this->grand_total = CartMangement::calculateGrandTotal($this->cart_items);
    }

    public function removeItem($product_id){
        $this->cart_items = CartMangement::removeCartItem($product_id);
        $this->grand_total = CartMangement::calculateGrandTotal($this->cart_items);
        $this->dispatch('update-cart-count', total_count: count($this->cart_items))->to(Navbar::class);
    }

    public function increaseQty($product_id){
        $this->cart_items = CartMangement::incrementQuantityToCartItem($product_id);
        $this->grand_total = CartMangement::calculateGrandTotal($this->cart_items);
    }

    public function decreaseQty($product_id){
        $this->cart_items = CartMangement::decrementQuantityToCartItem($product_id);
        $this->grand_total = CartMangement::calculateGrandTotal($this->cart_items);
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}