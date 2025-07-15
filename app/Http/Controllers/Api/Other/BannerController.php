<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function getBannersByType($type)
    {
        $banners = Banner::with('product')->orderBy('created_at', 'desc')->where('type', $type)->take(5)->get();
        return response()->json($banners);
    }

}
