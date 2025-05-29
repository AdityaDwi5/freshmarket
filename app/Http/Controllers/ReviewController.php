<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function index($productId)
    {
        try {
            $reviews = Review::select('reviews.*', 'users.name as user_name')
            ->join('users', 'users.id', '=', 'reviews.user_id')
            ->where('reviews.product_id', $productId)
            ->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada review untuk produk ini',
                'data' => null,
            ], 404);
        }

        return successResponse($reviews->map(function ($review) {
            return [
                'user' => $review->user_name,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->format('d-m-Y H:i:s'),
            ];
        }), 'Review berhasil diambil', 200);
        } catch (\Exception $e) {
            return errorResponse(null, $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
    // Validate the incoming data
    $validated = $request->validate([
        'reviews' => 'required|array',  // Expecting an array of reviews
        'reviews.*.product_id' => 'required|exists:products,id',  // Validate product ID for each review
        'reviews.*.rating' => 'required|integer|between:1,5',  // Validate rating for each review
        'reviews.*.comment' => 'nullable|string|max:500',  // Validate comment for each review
    ]);

    try {
        // Initialize an array to hold all the created reviews
        $reviews = [];

        // Loop through each review and create it in the database
        foreach ($validated['reviews'] as $reviewData) {
            $reviews[] = Review::create([
                'user_id' => auth('api')->id(),
                'product_id' => $reviewData['product_id'],
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'] ?? null,
            ]);
        }

        return successResponse($reviews, 'Reviews berhasil diambil', 201);
    } catch (\Exception $e) {
        return errorResponse(null, $e->getMessage(), 500);
    }
    }
}
