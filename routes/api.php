<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->group(function () {
Route::post('update_password',[UserController::class,'updatePassword']);
Route::post('update_profile',[UserController::class,'updateProfile']);
Route::get('get_profile',[UserController::class,'getProfile']);
Route::post('review', [ReviewController::class,'review']);
Route::get('all_reviews', [ReviewController::class, 'getAllReviews']);
Route::delete('all_reviews/{id}', [ReviewController::class, 'deleteReview']);


Route::middleware('admin')->group(function(){
    //Admin routes 
    Route::post('add_products', [AdminController::class, 'addProducts']);
    Route::put('all_product/{id}', [AdminController::class, 'updateProduct']);
    Route::delete('all_product/{id}', [AdminController::class, 'deleteProduct']);
});

Route::get('all_product',[UserController::class,'showAllProduct']);
Route::get('all_product/{id}', [UserController::class, 'show']);
Route::post('logout', [UserController::class,'logout']);


});
Route::post('register', [UserController::class,'register']);
Route::post('login', [UserController::class,'login']);
Route::post('forgot_password', [UserController::class,'forgotPassword']);
Route::post('verify_otp',[UserController::class,'verifyOtp']);


