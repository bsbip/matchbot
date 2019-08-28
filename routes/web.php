<?php

use App\Http\Controllers\MatchController;
use App\Http\Controllers\StatsController;
use Inertia\Inertia;

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

Route::get('/', [MatchController::class, 'getEventResults']);
Route::get('/stats', [StatsController::class, 'getTotalStats']);
Route::get('/standings', [StatsController::class, 'getDuoStats']);

Route::get('/players', function () {
    return Inertia::render('Players');
});

Route::get('/match', function () {
    return Inertia::render('Match');
});

Route::get('/results', [MatchController::class, 'getEvents']);
