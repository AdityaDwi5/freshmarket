<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Transaction;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Menambahkan produk ke dalam keranjang.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
    
        $user = Auth::user();
    
        // Ambil produk yang diminta
        $product = Product::find($request->product_id);
    
        // Cek apakah stok produk mencukupi
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Insufficient stock available'], 400);
        }
    
        // Ambil atau buat order untuk pengguna
        $order = Order::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'proses'],
            ['tracking_number' => $this->generateTrackingNumber()]
        );
    
        // Periksa apakah produk sudah ada dalam keranjang
        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $request->product_id)
            ->first();
    
        if ($orderItem) {
            // Jika sudah ada, tambahkan quantity baru
            $orderItem->quantity += $request->quantity;
            $orderItem->save();
        } else {
            // Jika belum ada, buat item baru di order
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }
    
        // Hitung ulang total harga
        $totalPrice = OrderItem::where('order_id', $order->id)
            ->sum(DB::raw('quantity * price'));
    
        $order->total_price = $totalPrice;
        $order->save();
    
        return response()->json(['message' => 'Product added to cart successfully'], 200);
    }

    /**
     * Mendapatkan item dalam keranjang.
     */
    public function getCart(Request $request)
    {
        $user = Auth::user();

        // Ambil order yang sedang diproses
        $order = Order::where('user_id', $user->id)
            ->where('status', 'proses')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Cart is empty'], 200);
        }

        $cartItems = OrderItem::where('order_id', $order->id)
            ->with('product')
            ->get();

        return response()->json($cartItems, 200);
    }

    /**
     * Checkout keranjang dengan pilihan pembayaran.
     */
    public function checkout(Request $request)
    {
        $request->validate(['payment_method' => 'required|in:midtrans,cash']);

        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->where('status', 'proses')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'No pending order found'], 404);
        }

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'order_id' => $order->id,
                'payment_status' => 'pending',
            ]);

            if ($request->payment_method === 'midtrans') {
                Config::$serverKey = config('services.midtrans.server_key');
                Config::$isProduction = config('services.midtrans.is_production');
                Config::$isSanitized = true;
                Config::$is3ds = true;

                $paymentData = [
                    'transaction_details' => [
                        'order_id' => $transaction->id,
                        'gross_amount' => $order->total_price,
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                        'address' => $user->address,
                    ],
                    'item_details' => $order->orderItems->map(function ($detail) {
                        return [
                            'id' => $detail->product_id,
                            'price' => $detail->price,
                            'quantity' => $detail->quantity,
                            'name' => $detail->product->name,
                        ];
                    })->toArray(),
                ];

                $snapToken = Snap::getSnapToken($paymentData);

                DB::commit();

                return response()->json(['snapToken' => $snapToken], 200);
            }

            if ($request->payment_method === 'cash') {
                foreach ($order->orderItems as $item) {
                    if ($item->product->stock < $item->quantity) {
                        return response()->json([
                            'message' => "Stok tidak mencukupi untuk produk: {$item->product->name}"
                        ], 400);
                    }
                }
            
                $order->update(['status' => 'selesai']);
                $transaction->update(['payment_status' => 'pending']);
            
                foreach ($order->orderItems as $item) {
                    $item->product->decrement('stock', $item->quantity);
                }
            
                DB::commit();
            
                return response()->json(['message' => 'Order completed successfully'], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus item dari keranjang.
     */
    public function removeFromCart($id)
    {
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)
            ->where('status', 'proses')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Cart is empty or order not found'], 404);
        }

        $orderItem = OrderItem::where('id', $id)
            ->where('order_id', $order->id)
            ->first();

        if (!$orderItem) {
            return response()->json(['message' => 'Item not found in the cart'], 404);
        }

        $orderItem->delete();

        $totalPrice = OrderItem::where('order_id', $order->id)
            ->sum(DB::raw('quantity * price'));

        $order->total_price = $totalPrice;
        $order->save();

        return response()->json(['message' => 'Item removed from cart successfully'], 200);
    }


    /**
     * Membuat nomor tracking unik.
     */
    private function generateTrackingNumber()
    {
        return strtoupper(uniqid('TRK-'));
    }

    public function updateQuantity(Request $request, $id)
    {
    $request->validate([
        'quantity' => 'required|integer|min:0',
    ]);

    // Find the OrderItem
    $cartItem = OrderItem::with('product')->find($id);

    if (!$cartItem) {
        return response()->json(['message' => 'Cart item not found'], 404);
    }

    $product = $cartItem->product;

    // Check if the stock is sufficient
    if ($product->stock < $request->quantity) {
        return response()->json([
            'message' => "Insufficient stock for product: {$product->name}",
        ], 400);
    }

    // Update the quantity in the cart
    $cartItem->quantity = $request->quantity;
    $cartItem->save();

    // Calculate the updated total price for the order item
    $cartItemTotalPrice = $cartItem->quantity * $product->price;

    // Update the total price in the order
    $order = $cartItem->order;  // assuming `OrderItem` belongs to `Order`

    // Recalculate the order's total price by summing up the total price of all items in the order
    $orderTotalPrice = $order->orderItems->sum(function ($orderItem) {
        return $orderItem->quantity * $orderItem->product->price;
    });

    // Update the total price of the order
    $order->total_price = $orderTotalPrice;
    $order->save();

    return response()->json([
        'message' => 'Quantity and total price updated successfully',
        'cart_item' => $cartItem,
        'order_total_price' => $order->total_price,
    ], 200);
    }

}
