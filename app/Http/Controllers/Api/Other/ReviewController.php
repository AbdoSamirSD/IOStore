<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function addReview(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Create the review
        $review = new Review();
        $review->product_id = $id;
        $review->user_id = auth()->id();
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return response()->json(['message' => 'Review added successfully', 
            'review' => [
                'id' => $review->id,
                'product_id' => $review->product_id,
                'user_id' => $review->user_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at,
            ]
    ], 201);
    }


    public function updateReview(Request $request, $id, $reviewId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $review = Review::find($reviewId);
        if (!$review) {
            return response()->json(['message' => 'Review not found '], 404);
        }

        if ($review->product_id != $id || $review->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Update the review
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return response()->json(['message' => 'Review updated successfully',
            'review' => $review
    ], 200);
    }

    public function deleteReview($id, $reviewId)
    {
        
        $review = Review::find($reviewId);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if ($review->product_id != $id || $review->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the review
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully'], 200);
    }
}
