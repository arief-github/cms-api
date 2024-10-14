<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\LoginController;
use App\Http\Controllers\Api\Admin\TagController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\MenuController;
use App\Http\Controllers\Api\Admin\SliderController;
use App\Http\Controllers\Api\Admin\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth Route
Route::prefix('admin')->group(function() {
    Route::post('/login', [LoginController::class, 'index']);

    // Group dengan prefix auth
    Route::group(['middleware' => 'auth:api'], function () {
       // GET data USER
        Route::get('/user', [LoginController::class, 'getUser']);
        // Refresh JWT Token
        Route::get('/refresh', [LoginController::class, 'refreshToken']);
        // Logout
        Route::post('/logout', [LoginController::class, 'logout']);

        // Tags
        Route::apiResource('/tags',TagController::class);

        // Category
        Route::apiResource('/categories', CategoryController::class);

        // Posts
        Route::apiResource('/posts', PostController::class);

        // Menu
        Route::apiResource('/menus', MenuController::class);

        // Slider
        Route::apiResource('/sliders', SliderController::class);

        // User
        Route::apiResource('/users', UserController::class);
    });
});
