<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});



use App\Http\Controllers\FileUploadController;
Route::get('file-upload', [ FileUploadController::class, 'getFileUploadForm' ])->name('get.fileupload');
Route::post('file-upload', [ FileUploadController::class, 'store' ])->name('store.file');
