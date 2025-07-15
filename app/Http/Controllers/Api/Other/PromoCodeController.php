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
        if ($promoCode && $promoCode->is_valid) {
            return response()->json([
                'message' => 'Promo code is valid',
                'promo_code' => $promoCode,
            ]);
        } else {
            return response()->json([
                'message' => 'Promo code is invalid',
            ], 403);
        }
    }
}
