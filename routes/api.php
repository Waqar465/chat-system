<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('usersignup', [\App\Http\Controllers\UserController::class, 'UserSignup']);
Route::post('userlogin', [\App\Http\Controllers\UserController::class, 'login']);
Route::post('logout', [\App\Http\Controllers\UserController::class, 'logout'])->middleware('check');
Route::post('message/send', [\App\Http\Controllers\MessageController::class, 'SendMessage'])->middleware('check');
Route::get('messages', [\App\Http\Controllers\MessageController::class, 'GetMessages'])->middleware('check');
Route::post('forgetpassword', [\App\Http\Controllers\ForgetController::class, 'ForgetPassword']);
Route::post('updatepassword/{token}', [\App\Http\Controllers\ForgetController::class, 'UpdatePassword']);
Route::post('updatemessage', [\App\Http\Controllers\MessageController::class, 'UpdateMessage'])->middleware('check');
Route::delete('deletemessage', [\App\Http\Controllers\MessageController::class, 'DeleteMessage'])->middleware('check');

