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



Auth::routes();

Route::get('home', function () {
    return view('home');
});
Route::get('/test', 'HomeController@index')->name('test');
//Route::get('/home/{id}', 'HomeController@show')->name('detail');
//Route::delete('/home/{id}', 'HomeController@destroy')->name('delete');




















