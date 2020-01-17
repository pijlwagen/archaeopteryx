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

Route::group([
    'prefix' => '/domains'
], function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/new', 'HomeController@create')->name('create');
    Route::post('/new', 'HomeController@handle')->name('handle');
    Route::get('/{domain}/files', 'Files\FileManagerController@index')->name('domain.files.index');
    Route::get('/{domain}/files/open', 'Files\FileManagerController@edit')->name('domain.files.edit');
});

Route::group([
    'namespace' => 'Auth'
], function () {
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'RegisterController@register');
});
