<?php

namespace App\Livewire;

use App\Helpers\CartMangement;
use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Stripe;

#[Title('Checkout')]
class CheckoutPage extends Component
{
    public $first_name;
    public $last_name;
    public $phone;
    public $address;
    public $city;
    public $state;
    public $payment_method;

    public function mount(){
        $cart_items = CartMangement::getCartItemsFromCookie();
        if(count($cart_items) == 0){
            return redirect('products');
        }
    }

    public function placeOrder(){
        
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'address' => 'required', 
            'city' => 'required',
            'state' => 'required',
            'payment_method' => 'required'
        ]);

        $cart_items = CartMangement::getCartItemsFromCookie();

        $line_items = [];

        foreach($cart_items as $item){
            $line_items[] =[
                'price_data' => [
                    'currency' => 'vnd',
                    'unit_amount' => $item['unit_amount'] * 100,
                    'product_data' => [
                        'name' => $item['name'],
                    ]
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->grand_total = CartMangement::calculateGrandTotal($cart_items);
        $order->payment_method = $this->payment_method;
        $order->payment_status = 'pending';
        $order->status = 'new';
        $order->currency = 'vnd';
        $order->shipping_amount = 0;
        $order->shipping_method = 'none';
        $order->notes = 'Order place by' . auth()->user()->name;

        $address = new Address();
        $address->first_name = $this->first_name;
        $address->last_name = $this->last_name;
        $address->phone = $this->phone;
        $address->address = $this->address;
        $address->city  = $this->city;
        $address->state = $this->state;

        $redirect_url = '';

        if($this->payment_method == 'stripe'){
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $sessionCheckout = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => auth()->user()->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel')
            ]);

            $redirect_url = $sessionCheckout->url;
        }else{
            $redirect_url = route('success');
        }

        $order->save();
        $address->order_id = $order->id;
        $address->save();
        $order->items()->createMany($cart_items);
        CartMangement::clearCartItems();
        Mail::to(request()->user())->send(new OrderPlaced($order));
        return redirect($redirect_url);
    }

    public function render()
    {
        $cart_items = CartMangement::getCartItemsFromCookie();
        $grand_total = CartMangement::calculateGrandTotal($cart_items);
        return view('livewire.checkout-page',[
            'cart_items' => $cart_items,
            'grand_total' => $grand_total
        ]);
    }
}