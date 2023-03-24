<?php

use App\Http\Controllers\API\{
    CategoryController,
    CommentController,
    FolderController,
    LoginController,
    PostController,
    SuperadminController
};
use App\Http\Controllers\{
    FacebookController,
    WebhookController
};
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

// Log In 
Route::post('login', [LoginController::class, 'login'])->name('loggedin');

// Facebook Authentication Routes
Route::controller(FacebookController::class)->group(function () {
    Route::group(['middleware' => ['web']], function () {
        Route::get('auth', 'loginUsingFacebook');
        Route::get('callback', 'callbackFromFacebook');
    });
});

Route::group(['middleware' => 'auth:api'], function () {

    // Admin
    Route::controller(PostController::class)->group(function () {
        Route::post('insert', 'insert_post');
        Route::get('search', 'search_post');
        Route::post('update/{id}', 'update_post');
        Route::delete('delete/{id}', 'delete_post');
        Route::get('list', 'list_post');
    });
    Route::controller(CommentController::class)->group(function () {
        Route::post('comment/{id}', 'comment');
        Route::get('postcommentlist/{id}', 'show');
        Route::get('commentpostlisting/{id}', 'showlist');
        Route::post('update_comment/{id}', 'update');
        Route::delete('delete_comment/{id}', 'delete');
    });
    Route::controller(CategoryController::class)->group(function () {
        Route::post('insertcategory', 'insert');
        Route::get('listcategory', 'list');
        Route::put('editcategory/{id}', 'edit');
        Route::delete('deletecategory/{id}', 'delete');
    });
    Route::controller(FolderController::class)->group(function () {
        Route::post('insert_folder', 'insert');
        Route::get('list_folder', 'list');
        Route::put('rename_folder/{id}', 'rename');
        Route::delete('delete_folder/{id}', 'delete');
        Route::post('restore_folder/{id}', 'restore');
        Route::delete('permanent_delete_folder/{id}', 'permanent_delete');
        Route::post('restore_all_folder', 'restore_all');
    });
    Route::get('logout', [LoginController::class, 'logged_out']);

    // Super Admin
    Route::get('listadmin', [SuperadminController::class, 'listadmin']);

    // Webhook
    Route::get('webhook', [WebhookController::class, 'backFromWebHook']);

    // Facebook
    Route::controller(FacebookController::class)->group(function () {
        Route::get('page_tokens', 'get_tokens_from_facebook');
    });
});
