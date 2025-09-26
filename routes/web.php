<?php

use App\Http\Controllers\CloakingController;
use App\Http\Controllers\JustOrangeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', action: [JustOrangeController::class , 'index']);
Route::get('/blank/{id}',[JustOrangeController::class,'blankPage'] );
Route::get('/v' , CloakingController::class);
Route::get('/s/{id}', CloakingController::class);
Route::get('/{id}' , CloakingController::class);
