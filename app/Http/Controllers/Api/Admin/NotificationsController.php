<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationsController extends Controller
{

    public function index()
    {

        $notifications = Notification::where('type', 'order')->with('notifiable')->latest()->paginate(50);
        return response()->json($notifications);
    }

    public function saveDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $deviceToken = $request->input('device_token');

        // Define the path to the JSON file
        $filePath = 'device_token.json';

        // Save the token to the JSON file
        Storage::put($filePath, json_encode(['device_token' => $deviceToken]));

        return response()->json([
            'message' => 'Device token saved successfully!',
            'device_token' => $deviceToken,
        ]);
    }

    public function getDeviceToken()
    {
        $filePath = 'device_token.json';

        if (Storage::exists($filePath)) {
            $data = json_decode(Storage::get($filePath), true);
            return response()->json($data);
        }

        return response()->json([
            'message' => 'No device token found.',
        ], 404);
    }
}
