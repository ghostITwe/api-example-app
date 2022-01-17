<?php

use \App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Публичные маршруты для просмотра API
//Работа с авторизацией и регистрацией
Route::post("/registration", [AuthController::class, 'register']);
Route::post("/auth", [AuthController::class, 'login']);

//Работа с новостями
Route::get('/posts', [PostController::class, 'getPosts']);
Route::get('/posts/{id}', [PostController::class, 'getPost']);
Route::get('/posts/tag/{name}', [PostController::class, 'searchPost']);

//Работа с комментариями public
Route::post('/posts/{id}/comments', [CommentController::class, 'createComment']);

//Защищенные маршруты через API токен
Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/posts', [PostController::class, 'create']);
    Route::post('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'delete']);
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'deleteComment']);
});


