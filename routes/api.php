<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function(){
    Route::post('/login', 'login');
    Route::get('/admin/fetch/token/{token}', 'fetch_by_verification_token');
    Route::post('/admin/activate', 'activate');
    Route::get('/recover-password/{email}', 'recover_password');
    Route::post('/reset_password', 'reset_password');
    Route::post('/add-admin', 'store_admin');
});

Route::middleware('auth:sanctum')->group(function(){
    Route::controller(AuthController::class)->group(function(){
        Route::get('/me', 'me');
        Route::get('/admins', 'index');
        Route::post('/admins', 'store');
        Route::get('/admins/{id}', 'show');
        Route::put('/admins/{id}', 'update');
        Route::post('/change-password', 'change_password');
        Route::delete('/admins/{id}', 'destroy');
    });

    Route::controller(TestimonialController::class)->group(function(){
        Route::get('/testimonials', 'index');
        Route::post('/testimonials', 'store');
        Route::get('/testimonials/{id}', 'show');
        Route::put('/testimonials/{id}', 'update');
        Route::delete('/testimonials/{id}', 'destroy');
    });

    Route::controller(PhotoGalleryController::class)->group(function(){
        Route::get('/photos', 'index');
        Route::post('/photos', 'store');
        Route::get('/photos/{id}', 'show');
        Route::put('/photos/{id}', 'update');
        Route::delete('/photos/{id}', 'destroy');
    });
});