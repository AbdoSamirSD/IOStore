<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string|max:255',
        ]);
        $promoCode = PromoCode::where('code', $request->promo_code)->first();

        if (!$promoCode){
            return response()->json([
                'message' => 'Promo code not found',
            ], 404);
        }
        
        if($promoCode->status !== 'active' || !now()->between($promoCode->start_date, $promoCode->end_date) || $promoCode->uses >= $promoCode->max_uses) {
        return response()->json([
                'message' => 'Promo code is invalid',
            ], 403);
        }

        return response()->json([
            'message' => 'Promo code is valid',
            'promo_code' => $promoCode,
        ]);
    }
}
