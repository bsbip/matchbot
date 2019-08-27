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

Route::get('/', 'MatchController@getEventResults');
Route::get('/stats', 'StatsController@getTotalStats');
Route::get('/standings', 'StatsController@getDuoStats');

Route::get('/players', function () {
    return Inertia::render('Players');
});

Route::get('/match', function () {
    return Inertia::render('Match');
});

Route::get('/results', function () {
    return Inertia::render('Results');
});
