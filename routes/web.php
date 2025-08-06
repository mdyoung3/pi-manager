<?php

use App\Http\Controllers\PiholeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('PiHoleForm');
});

Route::get('/urllist', function () {
    return Inertia::render('UrlList');
});

Route::prefix('api')->group(function () {
    Route::post('/pihole/temporary-disable', [PiholeController::class, 'submit']);
    Route::get('/urls', [PiholeController::class, 'index']);
    Route::delete('/urls/{url}', [PiholeController::class, 'destroy']);
});
