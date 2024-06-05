<?php

use App\Http\Controllers\GenerateController;
use App\Http\Controllers\RefreshController;
use App\Http\Controllers\ValidateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route API
Route::name('api.')
    ->prefix('api')
    ->group(function () {

        // Jwt Token
        Route::name('auth.')
            ->prefix('auth')
            ->group(function (){

                // V1
                Route::name('v1.')
                    ->prefix('v1')
                    ->group(function () {
                        Route::post('/generate-token', [GenerateController::class, 'generateToken']);
                        Route::post('/refresh-token', [RefreshController::class, 'refreshToken']);
                        Route::get('/validate-token/{token}', [ValidateController::class, 'validateToken']);
                    });
            });
    });
