<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'game'], function () {
    Route::get('/', [GameController::class, 'index']);
    Route::get('/{game}', [GameController::class, 'show']);
    Route::post('/', [GameController::class, 'store']);
    Route::put('/{game}', [GameController::class, 'update']);
    Route::delete('/{game}', [GameController::class, 'destroy']);
    Route::get('/{game}', [GameController::class, 'proposeCombination']);
    });
