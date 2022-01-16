<?php

use \App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Публичные маршруты для просмотра API
Route::post("/registration", [AuthController::class, 'register']);
Route::post("/auth", [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'getPosts']);

//Защищенные маршруты через API токен
Route::group(['middleware' => ['auth:sanctum']], function() {
//    Route::get('/user', function (Request $request) {
//        return $request->user();
//    });
    Route::post('/posts', [PostController::class, 'create']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


