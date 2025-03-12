<?php

use App\Http\Controllers\API\FoodController;
use App\Http\Controllers\API\InventoryLogController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderListController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\StockEntryController;
use App\Http\Controllers\API\StockItemController;
use App\Http\Controllers\API\TableController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/', function () {
        return [
            'success' => true,
            'version' => '1.0.0',
        ];
    });

    Route::apiResource('users', UserController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('orderLists', OrderListController::class);
    Route::apiResource('tables', TableController::class);
    Route::apiResource('reservations', ReservationController::class);
    Route::apiResource('foods', FoodController::class);
    Route::apiResource('stockItems', StockItemController::class);
    Route::apiResource('stockEntries', StockEntryController::class);
    Route::apiResource('inventoryLogs', InventoryLogController::class);
});


