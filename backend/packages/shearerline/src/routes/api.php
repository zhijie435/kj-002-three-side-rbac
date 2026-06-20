<?php

use Illuminate\Support\Facades\Route;
use Shearerline\Http\Controllers\Api\ShearerlineController;
use Shearerline\Http\Controllers\Api\ShearerlineTaskController;

Route::prefix('api')->group(function () {
    Route::prefix('shearers')->group(function () {
        Route::get('/', [ShearerlineController::class, 'index']);
        Route::get('/all', [ShearerlineController::class, 'all']);
        Route::get('/{id}', [ShearerlineController::class, 'show']);
        Route::post('/', [ShearerlineController::class, 'store']);
        Route::put('/{id}', [ShearerlineController::class, 'update']);
        Route::delete('/{id}', [ShearerlineController::class, 'destroy']);
        Route::patch('/{id}/{action}', [ShearerlineController::class, 'toggleStatus'])
            ->where('action', 'start|stop|maintenance|error');
    });

    Route::prefix('shearerline-tasks')->group(function () {
        Route::get('/', [ShearerlineTaskController::class, 'index']);
        Route::get('/{id}', [ShearerlineTaskController::class, 'show']);
        Route::post('/', [ShearerlineTaskController::class, 'store']);
        Route::put('/{id}', [ShearerlineTaskController::class, 'update']);
        Route::delete('/{id}', [ShearerlineTaskController::class, 'destroy']);
        Route::patch('/{id}/assign', [ShearerlineTaskController::class, 'assign']);
        Route::patch('/{id}/start', [ShearerlineTaskController::class, 'start']);
        Route::patch('/{id}/complete', [ShearerlineTaskController::class, 'complete']);
        Route::patch('/{id}/cancel', [ShearerlineTaskController::class, 'cancel']);
    });
});
