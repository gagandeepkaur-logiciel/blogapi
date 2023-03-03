<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\SuperadminController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\WebhookController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [LoginController::class, 'login'])->name('loggedin');

Route::group(['middleware' => 'auth:api'], function () {

    // Admin
    Route::controller(PostController::class)->group(function () {
        Route::post('insert', 'insertpost');
        Route::get('search', 'searchpost'); 
        Route::post('update/{id}', 'updatepost'); 
        Route::get('delete/{id}', 'deletepost'); 
        Route::get('post', 'show'); 
    });
    Route::controller(CommentController::class)->group(function () {
        Route::post('comment/{id}', 'comment'); 
        Route::get('postcommentlist/{id}', 'show'); 
        Route::get('commentpostlisting/{id}', 'showlist'); 
        Route::post('update_comment/{id}', 'update'); 
        Route::get('delete_comment/{id}', 'delete'); 
    });
    Route::controller(CategoryController::class)->group(function () { 
        Route::post('insertcategory', 'insert'); 
        Route::get('listcategory', 'list'); 
        Route::put('editcategory/{name}', 'edit'); 
        Route::delete('deletecategory/{name}', 'delete'); 
    });
    Route::get('logout', [LoginController::class, 'logged_out']); 

    // Super Admin
    Route::get('listadmin', [SuperadminController::class, 'listadmin']); 

    // Webhook
    Route::get('webhook', [WebhookController::class, 'backFromWebHook']); 
});
// Facebook Authentication Routes
Route::controller(FacebookController::class)->group(function () {
    Route::group(['middleware' => ['web']], function () {
        Route::get('auth', 'loginUsingFacebook'); 
        Route::get('callback', 'callbackFromFacebook'); 
    });
});

