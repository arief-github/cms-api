<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\LoginController;

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
    });
});
