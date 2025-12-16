<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/wallets', [WalletController::class, 'store']);
Route::get('/wallets', [WalletController::class, 'index']);
Route::get('/wallets/{id}', [WalletController::class, 'show']);
Route::get('/wallets/{id}/balance', [WalletController::class, 'balance']);

Route::post('/wallets/{id}/deposit', [TransactionController::class, 'deposit']);
Route::post('/wallets/{id}/withdraw', [TransactionController::class, 'withdraw']);
Route::get('/wallets/{id}/transactions', [TransactionController::class, 'index']);

Route::post('/transfers', [TransferController::class, 'store']);

Route::get('/health', [HealthController::class, 'index']);
