<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportProcessController;
use App\Http\Controllers\ImportProcessErrorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QueueHealthController;
use Illuminate\Support\Facades\Route;


Route::post('users', [UserController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::middleware('jwt')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::post('upload', [FileController::class, 'upload']);
    Route::get('import-status/{id}', ImportProcessController::class);
    Route::get('import-process/{id}/errors', ImportProcessErrorController::class);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::get('/worker-health', [QueueHealthController::class, 'check']);
