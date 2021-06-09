<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UploadController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get("/", [IndexController::class, 'index'])->name('home');
    Route::post("/load-more", [IndexController::class, 'load_more'])->name('index.load_more');

    /* Routes related to uploading files */
    Route::prefix('upload')->name("uploads.")->group(function () {
        Route::get("/", [UploadController::class, 'index'])->name('index');
        Route::post("store", [UploadController::class, 'store'])->name('store');
        Route::post("store-file", [UploadController::class, 'storeFile'])->name('store_file');
        Route::post("cancel-upload", [UploadController::class, 'cancelUpload'])->name('cancel_upload');
        Route::post("add-tag", [UploadController::class, 'addTag'])->name('add_tag');
        Route::post("remove-tag", [UploadController::class, 'removeTag'])->name('remove_tag');
        Route::post('update-details', [UploadController::class, 'updateDetails'])->name('update-details');
    });


    /* Routes related to photos */
    Route::get("/photo/{name}", [PhotoController::class, 'show'])->name('photos.show');
    Route::get("/trash", [PhotoController::class, 'trash'])->name('trash');


    /* Routes related to tags */
    Route::get("/tags/search-by-partial")->name('tags.search_partial');
    Route::get('/tags/get-tags', [TagController::class, 'getTags'])->name('tags.get_tags');
    Route::resource('tags', TagController::class);



    // Route::get("/dashboard", function () {
    //     return Inertia::render('Dashboard');
    // })->name('dashboard');
});
