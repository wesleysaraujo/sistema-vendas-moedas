<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrenciesController;
use App\Http\Controllers\Api\TransactionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::get('/up', function () {
    return response()->json(['status' => 'UP']);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas para moedas
Route::get('/currencies', [CurrenciesController::class, 'index']);
Route::get('/currencies/show/{code}', [CurrenciesController::class, 'show']);

// Rotas que exigem autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Rotas para autenticação
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rotas para transações
    Route::post('/transactions/simulate', [TransactionsController::class, 'simulate']);
    Route::post('/transactions', [TransactionsController::class, 'store']);
    Route::get('/transactions', [TransactionsController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionsController::class, 'show']);
});
