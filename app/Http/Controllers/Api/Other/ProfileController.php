<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Services\CustomAddress;
use Hash;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;

class ProfileController extends Controller
{
    use FileUploadTrait;

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadFile($request, 'image', oldPath: $user->image, path: '/uploads/profiles');
            $user->image = $imagePath;
        }

        $user->save();
        $user->refresh();
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updateAddress(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'city' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'street' => 'required|string|max:255',
        ]);
        $user->address = new CustomAddress(
            $request->city,
            $request->region,
            $request->street
        );
        $user->save();
        $user->refresh();

        return response()->json(['message' => 'Address updated successfully', 'user' => $user]);
    }
}
