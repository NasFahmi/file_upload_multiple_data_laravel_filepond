<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TemporaryImageController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/product', [ProductController::class, 'index'])->name('product.index');
Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');
Route::get('/product/{id}/edit', [ProductController::class, 'edit'])->name('product.edit');
// Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');
Route::delete('/product/{id}', [ProductController::class, 'store'])->name('product.destroy');

Route::post('/product/{id}/update', [ProductController::class, 'update'])->name('product.update');

Route::post('/product/upload',[TemporaryImageController::class,'uploadTemporary'])->name('upload.temporary');
Route::post('/product/revert',[TemporaryImageController::class,'deleteTemporary'])->name('delete.temporary');

Route::post('/product/update/{id}',[TemporaryImageController::class,'uploadImageDirectlyToDB'])->name('update.toDB');

Route::get('/product/test', [ProductController::class, 'test'])->name('product.test');