<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyRateController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// routes/api.php

Route::get('currency-rates', [CurrencyRateController::class, 'index']);
Route::get('fetch-currency-rates', [CurrencyRateController::class, 'fetchFromCbr']);

