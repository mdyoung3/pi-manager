<?php

use App\Http\Controllers\PiholeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('PiHoleManagementHub');
});

Route::get('/urllist', function () {
    return Inertia::render('UrlList');
});

Route::prefix('api')->group(function () {
    Route::post('/pihole/temporary-disable', [PiholeController::class, 'disablePihole']);
    Route::post('/pihole/add-url', [PiholeController::class, 'submit']);
    Route::get('/urls', [PiholeController::class, 'index']);
    Route::delete('/urls/{url}', [PiholeController::class, 'destroy']);

    // Blocked URLs endpoints
    Route::post('/blocked-urls', [PiholeController::class, 'storeBlockedUrl']);
    Route::get('/blocked-urls', [PiholeController::class, 'getBlockedUrls']);
    Route::delete('/blocked-urls/{blockedUrl}', [PiholeController::class, 'destroyBlockedUrl']);
});
