<?php

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

use App\Http\Controllers\InteractionController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\StatsController;

Route::group([
    'prefix' => 'slack',
    'middleware' => 'auth.token.slack',
], function () {
    Route::post('/match', [MatchController::class, 'create']);
    Route::post('/match/result', [MatchController::class, 'saveResultSlack']);
    Route::get('/stats', [StatsController::class, 'getResult']);
});

Route::group([
    'prefix' => 'slack',
    'middleware' => 'auth.signature.slack',
], function () {
    Route::post('/match/initiate', [MatchController::class, 'initiate']);
    Route::post('/interaction', [InteractionController::class, 'handle']);
});
