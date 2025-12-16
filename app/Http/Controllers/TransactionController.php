<?php

namespace App\Http\Controllers;

use App\Models\IdempotencyKey;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{

    public function index(Request $request, $id)
    {
        $wallet = Wallet::findOrFail($id);

        $query = $wallet->transactions()->orderBy('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return response()->json(
            $query->paginate(10)
        );
    }

    public function deposit(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $key = $request->header('Idempotency-Key');

        if ($key && IdempotencyKey::where('key', $key)->exists()) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        return DB::transaction(function () use ($request, $id, $key) {
            $wallet = Wallet::findOrFail($id);
            $wallet->increment('balance', $request->amount);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'idempotency_key' => $key
            ]);

            if ($key) {
                IdempotencyKey::create([
                    'key' => $key,
                    'action' => 'deposit'
                ]);
            }

            return response()->json(['balance' => $wallet->balance]);
        });
    }

    public function withdraw(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $key = $request->header('Idempotency-Key');

        if ($key && IdempotencyKey::where('key', $key)->exists()) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        return DB::transaction(function () use ($request, $id, $key) {
            $wallet = Wallet::findOrFail($id);

            if ($wallet->balance < $request->amount) {
                return response()->json(['error' => 'insufficient_funds'], 422);
            }

            $wallet->decrement('balance', $request->amount);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw',
                'amount' => $request->amount,
                'idempotency_key' => $key
            ]);

            if ($key) {
                IdempotencyKey::create([
                    'key' => $key,
                    'action' => 'withdraw'
                ]);
            }

            return response()->json(['balance' => $wallet->balance]);
        });
    }
}
