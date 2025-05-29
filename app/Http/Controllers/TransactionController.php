<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id; // Assuming user is authenticated

        $transactions = Transaction::with(['order.orderItems.product'])
            ->where('user_id', $userId)
            ->get();

        return response()->json($transactions);
    }

    /**
     * Checkout method for processing transactions.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array|min:1',
            'cart_items.*' => 'exists:cart_items,id',
        ]);

        $cartItemIds = $request->cart_items;

        DB::beginTransaction();
        try {
            $products = Product::whereIn('id', function ($query) use ($cartItemIds) {
                $query->select('product_id')
                    ->from('cart_items')
                    ->whereIn('id', $cartItemIds);
            })->lockForUpdate()->get();

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $products->sum(fn ($product) => $product->price * $product->cartItem->quantity),
            ]);

            foreach ($products as $product) {
                $cartItem = $product->cartItem;

                // Check product stock
                if ($product->stock < $cartItem->quantity) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok produk tidak mencukupi untuk ' . $product->name], 400);
                }

                // Reduce product stock
                $product->decrement('stock', $cartItem->quantity);

                // Create order items
                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $product->price,
                ]);
            }

            // Delete cart items after checkout
            CartItem::destroy($cartItemIds);

            // Create transaction
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'cashier_id' => Auth::id(),
                'payment_method' => 'cash',
                'payment_status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'order' => $order,
                'transaction' => $transaction,
                'message' => 'Checkout berhasil',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implement as needed
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Implement as needed
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Implement as needed
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Implement as needed
    }
}
