<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;



class ShoppingCartController extends Controller
{
    public function fetch(){
        $userId = Auth::id();
        $cart = ShoppingCart::with(
            ['items' => function($query) { 
                $query->where('status', 'open')
                    ->with(['product.images', 'variant.size']);
                }
            ])
            ->where('user_id', $userId)
            ->first();
        if(!$cart)  {
            ShoppingCart::create(['user_id' => $userId]);
            $cart = ShoppingCart::with(['items' => ['product.images', 'variant.size']])
                ->where('user_id', $userId)
                ->first();
        }
       return $cart;
    }

    public function update(Request $request) {

        $validated = $request->validate([
            'id' => ['required'],
            'quantity'=> ['integer'],
            'variant_id' => ['exists:product_variants,id'],
        ]);
        
        $cartItem = ShoppingCartItem::find( $validated['id']);     
        $cart = $this->fetch();

        $inCart = ShoppingCartItem::where('shopping_cart_id', $cart->id)->where('variant_id', $validated['variant_id'])->get();
        if(count($inCart) == 1){
            $item = $inCart->first();
            if($cartItem->variant->id != $item->variant->id){
                $variant = ProductVariant::find($item->variant->id);  
                if($item->quantity+1 <= $variant->stock){
                    if($item->status !== 'closed'){
                        $item->quantity = $item->quantity+1;
                    }
                    else{
                        $item->status = 'open';
                    }
                    $item->save();
                }
                $cartItem->status = 'closed';
                $cartItem->save();
                return redirect()->back();
            }            
            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();
            return redirect()->back();
        }
        
        $variant = ProductVariant::find($validated['variant_id']);
        $cartItem->variant_id = $variant->id;
        $cartItem->quantity = 1;
        $cartItem->status = 'open';
        $cartItem->save();
        
        return redirect()->back();
    }

    public function getCart(){
        $cart = $this->fetch();
        foreach($cart->items as $item){
            if($item->variant->stock <= 0){
                $item->status = 'closed';
                $item->save();
            }
        }
        $orders = Order::where('user_id', Auth::id())->get();
        return Inertia::render('Dynamic/ShoppingCart', 
        ['cart' => $cart->jsonify(), 
                'orders' => $orders->map( 
                function ($order)
                { 
                    return $order->jsonify();
                }
            )]);
    }

    public function addToCart(Request $request){
        $product_id = $request->input('product_id');
        $variant_id = $request->input('variant_id');
        $quantity = $request->input('quantity');
        $cart = $this->fetch();

        $item = ShoppingCartItem::where('shopping_cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->where('variant_id', $variant_id)
            ->first();

        if($item){
            if($item->status === 'closed'){
                $item->status = 'open';
                $item->quantity = $quantity;
                $item->save();
            }
            else if($item->quantity+$quantity <= $item->variant->stock){
                $item->quantity = $item->quantity + $quantity;
                $item->save();
            }
        }
        else{
            ShoppingCartItem::create(
                [
                    'shopping_cart_id' => $cart->id,
                    'product_id' => $request->input('product_id'),
                    'variant_id' => $request->input('variant_id'), 
                    'quantity' => $request->input('quantity'), 
                    'price' => $request->input('price')
                ]);        
        }
        return redirect()->back();
    }

    public function checkout(Request $request)
    {
        return Inertia::render('Dynamic/Checkout', ['orders' => $request->all()]);
    }

    public function confirmOrder(Request $request){
        $order = Order::create([
            'user_id' => Auth::id(),
            'total' => $request->input("total_price")
        ]);
    
        $items = $request->input('selected_items');
        foreach ($items as $item) {
            $cart_item = ShoppingCartItem::find($item['id']);
            OrderItem::create([
                'order_id' => $order->id,
                'shopping_cart_item_id' => $item['id']
            ]);
            $variant = ProductVariant::find($item['variant_id_on_cart']);
            if ($variant && $variant->stock > 0) {
                $variant->stock -= $cart_item->quantity;
                $variant->save();
            }
            $cart_item->status = 'closed';
            $cart_item->save();
        }
        return Inertia::render('Dynamic/OrderConfirmed', ['order' => $order]);
    }

    public function buy(Request $request){
        $product_id = $request->input('product_id');
        $variant_id = $request->input('variant_id');
        $quantity = $request->input('quantity');
        $cart = $this->fetch();

        $item = ShoppingCartItem::where('shopping_cart_id', $cart->id)
            ->where('product_id', $product_id)
            ->where('variant_id', $variant_id)
            ->first();

        if($item){
            if($item->status === 'closed'){
                $item->status = 'open';
                $item->quantity = $quantity;
                $item->save();
            }
        }
        else{
            $item = ShoppingCartItem::create(
                [
                    'shopping_cart_id' => $cart->id,
                    'product_id' => $request->input('product_id'),
                    'variant_id' => $request->input('variant_id'), 
                    'quantity' => $request->input('quantity'), 
                    'price' => $request->input('price')
                ]);        
        }   
        return Inertia::render('Dynamic/Checkout', ['orders' => ['selected_items' => [$item->jsonify()], 'total_price' => $request->input('price')]]);
    }

    public function destroy(Request $request){
        $items = $request->input('selected_items');
        foreach($items as $item){
            $item = ShoppingCartItem::find($item['id']);
            $item->status = 'closed';
            $item->save();
        }
        return redirect()->back();
    }

}
