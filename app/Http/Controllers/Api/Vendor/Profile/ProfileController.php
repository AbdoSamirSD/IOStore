<?php

namespace App\Http\Controllers\Api\Vendor\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request){
        return response()->json([
            'message' => 'Profile details retrieved successfully.',
            'vendor' => $request->user()
        ]);
    }

    public function update(Request $request){
        
        $vendor = $request->user();
        $validate = $request->validate([
            'full_name' => 'string|max:50',
            'store_name' => 'string|max:25',
            'phone' => 'string|max:15',
            'address' => 'nullable|string|max:255',
            'profile_image' => 'image|mimes:jpeg,png,jpg|max:10240',
            'expected_delivery_time' => 'nullable|integer|min:1|max:7', // 1 day to 7 days
        ]);

        $data = $request->only(['full_name', 'store_name', 'phone', 'address', 'expected_delivery_time']);

        if ($request->hasFile('profile_image')) {
            if ($vendor->profile_image && file_exists(storage_path('app/public/' . $vendor->profile_image))) {
                unlink(storage_path('app/public/' . $vendor->profile_image));
            }

            $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        $vendor->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'vendor' => $vendor,
        ]);
    }

    public function changePassword(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $vendor = $request->user();

        if (!Hash::check($request->current_password, $vendor->getAuthPassword())) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $vendor->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function destroy(Request $request){
        
        $vendor = $request->user();
        $vendor->tokens()->delete();

        if ($vendor->profile_image) {
            Storage::disk('public')->delete($vendor->profile_image);
        }

        if ($vendor->commercial_register) {
            Storage::disk('public')->delete($vendor->commercial_register);
        }

        $vendor->delete();

        return response()->json(['message' => 'Profile deleted successfully.']);
    }

    public function updateStatus(Request $request){
        $vendor = $request->user();
        $request->validate([
            'status' => 'required|boolean',
        ]);
        $vendor->update(['status' => $request->store_status]);

        return response()->json([
            'message' => 'Store status updated successfully.',
            'status' => $vendor->store_status,
        ]);
    }
}
