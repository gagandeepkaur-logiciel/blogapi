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
        Route::post('insert', 'insertpost'); // Insert post
        Route::get('search', 'searchpost'); // Search Post
        Route::post('update/{id}', 'updatepost'); // Update post
        Route::get('delete', 'deletepost'); // Delete Post
        Route::get('post', 'show'); // Post listing
    });
    Route::controller(CommentController::class)->group(function () {
        Route::post('comment/{id}', 'comment'); // Insert comment
        Route::get('postcommentlist/{id}', 'show'); // Post Comment Listing
        Route::get('commentpostlisting/{id}', 'showlist'); // Comment Post Listing
    });
    Route::controller(CategoryController::class)->group(function () { // Catgeory
        Route::post('insertcategory', 'insert'); // Insert category
        Route::get('listcategory', 'list'); // Listing
        Route::put('editcategory/{name}', 'edit'); // Edit category
        Route::delete('deletecategory/{name}', 'delete'); // Delete category
    });
    Route::get('logout', [LoginController::class, 'logged_out']); // Logout

    // Super Admin
    Route::get('listadmin', [SuperadminController::class, 'listadmin']); //Admin listing

    // Webhook
    Route::get('webhook', [WebhookController::class, 'backFromWebHook']); // Redirect
});
// Facebook Authentication Routes
Route::controller(FacebookController::class)->group(function () {
    Route::group(['middleware' => ['web']], function () {
        Route::get('auth', 'loginUsingFacebook'); // Login
        Route::get('callback', 'callbackFromFacebook'); // Redirect
    });
});
