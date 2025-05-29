<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class KasirController extends Controller
{
    public function index()
    {
        return response()->json(Transaction::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|uuid',
            'order_id' => 'required|uuid',
            'payment_status' => 'required|string',
        ]);

        $transaction = Transaction::create($data);

        return response()->json($transaction, 201);
    }

    public function show($id)
    {
        return response()->json(Transaction::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $data = $request->validate([
            'user_id' => 'required|uuid',
            'order_id' => 'required|uuid',
            'payment_status' => 'required|string',
        ]);

        $transaction->update($data);

        return response()->json($transaction);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(null, 204);
    }
}
