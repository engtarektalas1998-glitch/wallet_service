<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{


    public function index(Request $request)
    {
        $query = Wallet::query();

        if ($request->filled('owner')) {
            $query->where('owner_name', $request->owner);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_name' => 'required|string',
            'currency' => 'required|string'
        ]);

        $wallet = Wallet::create($data);

        return response()->json($wallet, 201);
    }

    public function show($id)
    {
        $wallet = Wallet::findOrFail($id);

        return response()->json($wallet);
    }

    public function balance($id)
    {
        $wallet = Wallet::findOrFail($id);

        return response()->json([
            'balance' => $wallet->balance
        ]);
    }
}
