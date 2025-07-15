<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(10);

        return response()->json(
            $notifications
        );
    }
    public function unread(Request $request)
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications()->latest()->get();

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($notificationId);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    public function storeDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = auth()->user();

        // Create or update the device token
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $request->token, 'user_id' => $user->id],
            ['token' => $request->token, 'user_id' => $user->id]
        );

        return response()->json(['message' => 'Device token saved successfully', 'device_token' => $deviceToken]);
    }
    public function destroyDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = auth()->user();

        // Delete the device token
        DeviceToken::where('token', $request->token)->where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Device token deleted successfully']);
    }
}
