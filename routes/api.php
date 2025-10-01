<?php

use App\Http\Controllers\API\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories',[CategoryController::class,'index'])->name('api.categories.index');
Route::get('/category/{id}',[CategoryController::class,'getFromId'])->name('api.category.getFromId');
Route::get('/product/category/{category_id}',[App\Http\Controllers\API\ProductController::class,'getFromCategory'])->name('api.product.getFromCategory');
Route::get('/products',[App\Http\Controllers\API\ProductController::class,'index'])->name('api.products.index');
Route::get('/product/{id}',[App\Http\Controllers\API\ProductController::class,'getFromId'])->name('api.product.getFromId');

Route::any('/payment/callback', function(){
    return response()->json(
        [
            'success' => true,
            'message' => 'Payment callback received'
        ],200,[],JSON_PRETTY_PRINT);
        
});