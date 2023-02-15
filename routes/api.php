<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\SuperadminController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CommentController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin

Route::post('login', [LoginController::class, 'login'])->name('loggedin');

Route::group(['middleware' => 'auth:api'], function () {

    Route::controller(PostController::class)->group(function () {
        Route::post('insert', 'insertpost');     // Insert post
        Route::get('search', 'searchpost');      // Search Post
        Route::get('update/{id}', 'updatepost');  // Update post
        Route::get('delete', 'deletepost');      // Delete Post
        Route::get('post', 'show');              // Post listing
        Route::get('at', 'atoken');
        // Route::post('facebookdata', 'getdata');
    });
    Route::controller(CommentController::class)->group(function() {
        Route::post('comment/{id}', 'comment');          // Insert comment
    });
    Route::controller(CategoryController::class)->group(function () {                 // Catgeory
        Route::post('insertcategory', 'insert');
        Route::get('listcategory', 'list');
        Route::put('editcategory/{name}', 'edit');
        Route::delete('deletecategory/{name}', 'delete');
    });
    Route::get('logout', [LoginController::class, 'logged_out']);                      // Logout
});

// Super Admin
Route::group(['middleware' => 'auth:api'], function () {
    Route::get('listadmin', [SuperadminController::class, 'listadmin']);               //Admin listing
});

// Facebook Authentication Routes
Route::group(['middleware' => ['web']], function () {
    Route::get('auth', [FacebookController::class, 'loginUsingFacebook']);             // Login
    Route::get('callback', [FacebookController::class, 'callbackFromFacebook']);       // Redirect
    Route::get('webhook', [FacebookController::class, 'backFromWebHook']);             // Redirect
});