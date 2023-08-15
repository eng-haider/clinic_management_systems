<?php

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


Route::get('/asiahawala', function(){
    return view('asiahawala');
});


Route::get('/zaincashpay', function(){
    return view('zaincashpay');
});


Route::get('/st', function(){
    return "ggggggggggggggg";
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
