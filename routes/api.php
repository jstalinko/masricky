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
Route::post('/register',[App\Http\Controllers\API\AuthController::class,'register'])->name('api.auth.register');
Route::get('/user/{telegram_id}',[App\Http\Controllers\API\AuthController::class,'getFromTelegram'])->name('api.user.getFromTelegram');
Route::get('/order/history/{telegram_id}',[App\Http\Controllers\API\OrderController::class,'getOrderHistory'])->name('api.order.getOrderHistory');
Route::post('/order/create',[App\Http\Controllers\API\OrderController::class,'createOrder'])->name('api.order.createOrder');
Route::get('/order/cancel/{invoice_id}',[App\Http\Controllers\API\OrderController::class,'cancelOrder'])->name('api.order.cancelOrder');

Route::any('/payment/webhook-invoice', [App\Http\Controllers\API\WebhookController::class, 'webhookInvoice'])->name('api.payment.webhook-invoice');