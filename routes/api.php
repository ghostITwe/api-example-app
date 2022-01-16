<?php

use \App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Публичные маршруты для просмотра API
Route::post("/registration", [AuthController::class, 'register']);
Route::post("/auth", [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'getPosts']);
Route::get('/posts/{id}', [PostController::class, 'getPost']);
Route::get('/posts/tag/{name}', [PostController::class, 'searchPost']);

//Защищенные маршруты через API токен
Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/posts', [PostController::class, 'create']);
    Route::post('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'delete']);
});


