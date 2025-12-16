<?php

namespace App\Http\Controllers;

use App\Models\IdempotencyKey;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_wallet_id' => 'required|integer',
            'to_wallet_id' => 'required|integer',
            'amount' => 'required|integer|min:1'
        ]);

        if ($data['from_wallet_id'] === $data['to_wallet_id']) {
            return response()->json(['error' => 'self_transfer'], 422);
        }

        $key = $request->header('Idempotency-Key');

        if ($key && IdempotencyKey::where('key', $key)->exists()) {
            return response()->json(['status' => 'duplicate'], 200);
        }

        return DB::transaction(function () use ($data, $key) {
            $from = Wallet::findOrFail($data['from_wallet_id']);
            $to = Wallet::findOrFail($data['to_wallet_id']);

            if ($from->currency !== $to->currency) {
                return response()->json(['error' => 'currency_mismatch'], 422);
            }

            if ($from->balance < $data['amount']) {
                return response()->json(['error' => 'insufficient_funds'], 422);
            }

            $from->decrement('balance', $data['amount']);
            $to->increment('balance', $data['amount']);

            Transaction::create([
                'wallet_id' => $from->id,
                'type' => 'transfer_debit',
                'amount' => $data['amount'],
                'related_wallet_id' => $to->id,
                'idempotency_key' => $key
            ]);

            Transaction::create([
                'wallet_id' => $to->id,
                'type' => 'transfer_credit',
                'amount' => $data['amount'],
                'related_wallet_id' => $from->id,
                'idempotency_key' => $key
            ]);

            if ($key) {
                IdempotencyKey::create([
                    'key' => $key,
                    'action' => 'transfer'
                ]);
            }

            return response()->json(['status' => 'success']);
        });
    }

}
