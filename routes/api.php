<?php

use Illuminate\Http\Request;
use Ryanhs\Chess\Chess;

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

Route::any('/', 'HomeController@action');
Route::any('/test', function(){
    $chess = new Chess();
    $moves = $chess->moves();
    dd($moves);
});
Route::post('/images', 'HomeController@createImage');
