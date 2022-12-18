<?php

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

Route::match(['GET', 'HEAD'], 'config/{name}', 'ClashController@config')
   ->where('name', '[A-Za-z0-9-]+');
Route::match(['GET', 'HEAD'], 'proxies/{name}', 'ClashController@proxies')
    ->where('name', '[A-Za-z0-9-]+');

Route::middleware('shield:cray')->post('server/update', 'ClashController@updateServer');
