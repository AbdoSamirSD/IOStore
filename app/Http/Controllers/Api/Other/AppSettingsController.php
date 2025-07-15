<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppSettingsController extends Controller
{
    public function setAppVersion(Request $request)
    {
        $data = $request->validate([
            'android_version' => 'required|string',
            'android_url' => 'required|string',
            'android_end_date' => 'required|date',
            'ios_version' => 'required|string',
            'ios_end_date' => 'required|date',
            'ios_url' => 'required|string',
        ]);

        // Save to JSON file
        Storage::disk('local')->put('app_settings.json', json_encode($data));

        return response()->json(['message' => 'تم حفظ الأصدارات بنجاح.']);
    }

    function getAppVersion()
    {
        $data = json_decode(Storage::disk('local')->get('app_settings.json'), true);

        return response()->json($data);
    }
}
