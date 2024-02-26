<?php

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
   

});


Route::post('register', [UserController::class,'register']);
Route::post('login', [UserController::class,'login']);
Route::post('forgot_password', [UserController::class,'forgotPassword']);
Route::post('verify_otp',[UserController::class,'verifyOtp']);

