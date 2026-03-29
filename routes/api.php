<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StationController; 
use App\Http\Controllers\ConnectorTypeController;
use App\Http\Controllers\ReservationController; 

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    
    Route::get('/stations', [StationController::class, 'index']);
    Route::get('/stations/{id}', [StationController::class, 'show']);

    
    Route::get('/connector-types', [ConnectorTypeController::class, 'index']);

    
    Route::get('/my-reservations', [ReservationController::class, 'index']); 
    Route::post('/reservations', [ReservationController::class, 'store']); 
    Route::put('/reservations/{id}', [ReservationController::class, 'update']); 
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']); 

    Route::middleware('admin')->group(function () {
        
        Route::post('/stations', [StationController::class, 'store']);
        Route::put('/stations/{id}', [StationController::class, 'update']);
        Route::delete('/stations/{id}', [StationController::class, 'destroy']);

        Route::post('/connector-types', [ConnectorTypeController::class, 'store']);
        Route::put('/connector-types/{id}', [ConnectorTypeController::class, 'update']);
        Route::delete('/connector-types/{id}', [ConnectorTypeController::class, 'destroy']);

        Route::get('/admin/stats', [StationController::class, 'getStats']);
    });
});