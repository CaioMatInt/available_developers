<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::prefix('authentication')->group(function () {
    Route::post('login', [UserController::class, 'login'])->name('user.login');
    Route::post('register', [UserController::class, 'register'])->name('user.register');
    Route::post('password/forgot', [UserController::class, 'sendPasswordResetLinkEmail'])->name('user.password.forgot');
    Route::post('password/reset', [UserController::class, 'resetPassword'])->name('user.password.reset');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [UserController::class, 'logout'])->name('user.logout');
        Route::get('me', [UserController::class, 'getAuthenticatedUser'])->name('user.me');
    });
});
