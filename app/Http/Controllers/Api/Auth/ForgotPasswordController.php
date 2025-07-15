<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lang;

class ForgotPasswordController extends Controller
{
    // Forgot Password function
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:' . User::class],
        ]);

        $tokenData = DB::table('password_reset_tokens')->where('email', $request->email);
        if ($tokenData) {
            $tokenData->delete();
        }
        $token = mt_rand(100000, 999999);
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);
        Mail::to($request->email)->send(new ResetPasswordMail($token));
        return response()->json(['message' => 'Reset password code sent to your email']);
    }

    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'exists:users'],
        ]);
        // Retrieve the most recent token record
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->latest('created_at') // Get the most recent token
            ->first();

        if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        // Check if the token has expired
        $createdAt = Carbon::parse($tokenData->created_at);
        if ($createdAt->addMinutes(config('auth.passwords.users.expire'))->isPast()) {
            DB::table('password_reset_tokens')->where('email', $tokenData->email)->delete();
            return response()->json(['message' => 'Token has expired'], 400);
        }
        // Return JSON response 
        return response()->json([
            'message' => 'Token is valid',
            'is_valide' => true,
            'token' => $request->token
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Retrieve the most recent token record
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->latest('created_at') // Get the most recent token
            ->first();

        if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $createdAt = Carbon::parse($tokenData->created_at);
        if ($createdAt->addMinutes(config('auth.passwords.users.expire'))->isPast()) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 400);
        }

        // Delete token from password resets table
        $user = User::where('email', $tokenData->email)->firstOrFail();
        if ($user) {
            $user->update(
                ['password' => Hash::make($request->password),]
            );
        }
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        return response()->json([
            'message' => trans('auth.password_reset_success'),
            'status' => true
        ]);
    }
}
