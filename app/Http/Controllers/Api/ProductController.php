<?php
namespace App\Http\Controllers\Api;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     

    public function index()
    {
        $products = Product::all()->map(function ($product){
            $product->averageRating = $product->averageRating;
            return $product;
        });

        return response()->json($products);
    }

    public function search(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'query' => 'required|string|max:255',
        ]);
    
        // Get the search query from the request
        $query = $request->input('query');
    
        // Search for products by name or description
        $products = Product::with('reviews')  // Eager load reviews if needed
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->get();
    
        // Calculate averageRating dynamically for each product based on reviews
        $products = $products->map(function ($product) {
            // If product has reviews, calculate the average rating
            $product->averageRating = $product->reviews->avg('rating');
            
            // If there are no reviews, you can set it to null or a default value (e.g., 0)
            if ($product->reviews->isEmpty()) {
                $product->averageRating = null;
            }
    
            return $product;
        });
    
        // Return the products with average ratings
        return response()->json($products, 200);
    }
    


    public function getProductsByCategory($categoryName)
{
    $category = Category::where('name', $categoryName)->first();

    if (!$category) {
        return response()->json(['message' => 'Category not found'], 404);
    }

    $products = Product::where('category_id', $category->id)
        ->with('category') // Jika Anda ingin menyertakan data kategori
        ->get()
        ->map(function ($product) {
            $averageRating = Review::where('product_id', $product->id)->avg('rating');
            $product->averageRating = $averageRating ? number_format($averageRating, 1) : 0;
            return $product;
        });

    return response()->json($products);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Create new product
        $product = new Product();
        $product->name = $request->input('name');
        $product->price = $request->input('price');
        $product->category_id = $request->input('category_id');
        $product->description = $request->input('description');
        $product->stock = $request->input('stock');

        // Handle image upload if exists
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        // Save product to database
        $product->save();

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the product with the related category and reviews, including the user's name
        $product = Product::with(['category', 'reviews.user'])->find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        // Calculate the average rating for the product
        $averageRating = $product->reviews()->avg('rating');
    
        // Format the reviews to include the user's name instead of user_id
        $reviews = $product->reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user_name' => $review->user->name, // Get the user's name
            ];
        });
    
        return response()->json([
            'product' => $product,
            'averageRating' => $averageRating,
            'reviews' => $reviews, // Return the modified reviews
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Update product properties
        $product->name = $request->input('name', $product->name);
        $product->price = $request->input('price', $product->price);
        $product->category_id = $request->input('category_id', $product->category_id);
        $product->description = $request->input('description', $product->description);
        $product->stock = $request->input('stock', $product->stock);

        // Handle image upload if exists
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        }

        // Save updated product
        $product->save();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }
}

