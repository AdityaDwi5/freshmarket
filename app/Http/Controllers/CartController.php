<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;

class CartController extends Controller
{
    public function show()
    {
        $cart = Cart::with('items.product.promotions')->where('user_id', Auth::id())->first();;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong']);
        }

        $cartItems = $cart->items->map(function ($item) {
            return [
                'cart_item_id' => $item->id,
                'product' => new ProductResource($item->product->load('promotions')),
                'quantity' => $item->quantity,
            ];
        });

        return response()->json(['cart_items' => $cartItems]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stok produk tidak mencukupi'], 400);
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cartItem = $cart->items()->where('product_id', $request->product_id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;

            if ($product->stock < $newQuantity) {
                return response()->json(['message' => 'Stok produk tidak mencukupi'], 400);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['cart_item' => $cartItem, 'message' => 'Produk ditambahkan ke keranjang']);
    }

    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::findOrFail($cartItemId);

        if ($cartItem->cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product = $cartItem->product;

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stok produk tidak mencukupi'], 400);
        }

        if ($request->quantity <= 0) {
            $cartItem->delete();
            return response()->json(['message' => 'Produk dihapus dari keranjang karena kuantitas 0']);
        } else {
            $cartItem->update(['quantity' => $request->quantity]);

            return response()->json([
                'cart_item' => [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'created_at' => $cartItem->created_at->toIso8601String(),
                    'updated_at' => $cartItem->updated_at->toIso8601String(),
                ],
                'message' => 'Kuantitas produk berhasil diperbarui'
            ]);
        }
    }

    public function remove($cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);

        if ($cartItem->cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Produk dihapus dari keranjang']);
    }



    
}
