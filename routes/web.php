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
  return redirect('home');
});


//Auth::routes();

Route::get('/home', 'HomeController@index');

Route::resource('blogs', 'blogController');


Route::get('/getRedis','TestController@index');


Route::any('/serve', 'AppBaseController@index');

Route::any('/create_menu','AppBaseController@createMenu');

Route::any('/web_index','WechatController@webIndex');
Route::any('/get_web_access_token','WechatController@getWebAccessToken');


Route::any('/test_queue','TestController@testQueue');

