<?php

namespace App\Http\Controllers\Api\Vendor\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function wallet()
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }
        
        $wallet = $vendor->wallet;
        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found.',
            ], 404);
        }


        return response()->json([
            'message' => 'Wallet information retrieved successfully.',
            'data' => [
                'wallet_id' => $wallet->id,
                'balance' => $wallet->balance,
                'pending_balance' => $wallet->pending_balance,
                'total_earnings' => $wallet->total_earnings,
                'total_withdrawn' => $wallet->total_withdrawn,
                'total_refunded' => $wallet->total_refunded,
                'total_commission' => $wallet->total_commission,
                'created_at' => $wallet->created_at->toDateTimeString(),
                'updated_at' => $wallet->updated_at->toDateTimeString(),
            ],
        ]);
    }

    public function summary()
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        $wallet = $vendor->wallet;
        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Wallet summary retrieved successfully.',
            'data' => [
                'available_balance' => $wallet->balance,
                'pending_balance' => $wallet->pending_balance,
                'total_earnings' => $wallet->total_earnings,
                'total_withdrawn' => $wallet->total_withdrawn,
                'total_refunded' => $wallet->total_refunded,
            ],
        ]);
    }

    public function transactions()
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        $transactions = $vendor->wallet->transactions()->paginate(20);

        return response()->json([
            'message' => 'Transactions retrieved successfully.',
            'data' => $transactions,
        ]);
    }

    public function requestWithdraw(Request $request)
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:' . auth()->user()->wallet->balance,
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $amount = $request->input('amount');
        $wallet = auth()->user()->wallet;

        $wallet->transactions()->create([
            'type' => 'withdraw',
            'amount' => $amount,
            'description' => $request->input('description', 'Withdrawal request'),
            'direction' => 'debit',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'withdraw request created successfully',
            'withdraw_amount' => $amount,
        ], 201);
    }

    public function withdrawRequests()
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        $withdrawRequests = $vendor->wallet->transactions()
            ->where('type', 'withdraw')
            ->where('status', 'approved')
            ->get();
        if ($withdrawRequests->isEmpty()) {
            return response()->json([
                'message' => 'No withdrawal requests found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Withdrawal requests retrieved successfully.',
            'data' => [
                'amount' => $withdrawRequests->pluck('amount'),
                'date' => $withdrawRequests->pluck('created_at'),
                'total_amount' => $withdrawRequests->sum('amount'),
            ],
        ]);
    }

    public function withdrawRequestDetails($id)
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        $withdrawRequest = $vendor->wallet->transactions()
            ->where('type', 'withdraw')
            ->where('status', 'approved')
            ->where('id', $id)
            ->first();
        if (!$withdrawRequest) {
            return response()->json([
                'message' => 'Withdrawal request not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Withdrawal request details retrieved successfully.',
            'data' => $withdrawRequest,
        ]);
    }
}
