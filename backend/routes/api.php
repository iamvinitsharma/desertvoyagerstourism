<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

// Public website API (read by the Astro server, prefix: /api)
Route::get('/catalog', [CatalogController::class, 'catalog']);
Route::get('/content', [CatalogController::class, 'content']);
Route::get('/availability/{slug}', [CatalogController::class, 'availability']);

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/enquiries', [BookingController::class, 'storeEnquiry']);
    Route::post('/orders', [BookingController::class, 'storeOrder']);
    Route::get('/orders/{ref}', [BookingController::class, 'showOrder']);
    Route::post('/subscribe', [BookingController::class, 'subscribe']);
});
