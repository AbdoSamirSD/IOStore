<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()->favorites()->get()->pluck('product');
        return response()->json($favorites);
    }
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = auth()->id();
        $productId = $request->product_id;

        $favorite = Favorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['favorite' => false]);
        } else {
            Favorite::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            return response()->json(['favorite' => true]);
        }
    }

}
