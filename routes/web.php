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

Route::get('/createLinkToken', 'App\Http\Controllers\PlaidController@createLinkToken');
Route::post('/storePlaidAccount', 'App\Http\Controllers\PlaidController@storePlaidAccount');
Route::post('/getInvestmentHoldings', 'App\Http\Controllers\PlaidController@getInvestmentHoldings');

