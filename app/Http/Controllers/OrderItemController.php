<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index()
    {
        // Get all order items with their related order and product
        $orderItems = OrderItem::with(['order', 'product'])->get();

        return response()->json($orderItems);
    }

    public function store(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'order_id' => 'required|uuid',
            'product_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        // Create a new order item
        $orderItem = OrderItem::create($data);

        return response()->json($orderItem, 201);
    }

    public function show($id)
    {
        // Find the order item by ID and load related data
        $orderItem = OrderItem::with(['order', 'product'])->findOrFail($id);

        return response()->json($orderItem);
    }

    public function update(Request $request, $id)
    {
        // Find the order item
        $orderItem = OrderItem::findOrFail($id);

        // Validate and update order item data
        $data = $request->validate([
            'order_id' => 'required|uuid',
            'product_id' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $orderItem->update($data);

        return response()->json($orderItem);
    }

    public function destroy($id)
    {
        // Find the order item and delete it
        $orderItem = OrderItem::findOrFail($id);
        $orderItem->delete();

        return response()->json(null, 204);
    }
}
