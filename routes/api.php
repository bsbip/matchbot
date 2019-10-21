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
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\StatsController;

Route::group(['prefix' => 'slack'], function () {
    Route::group([
        'middleware' => 'auth.signature.slack',
    ], function () {
        Route::group(['prefix' => 'match'], function () {
            Route::post('/', [MatchController::class, 'create']);
            Route::post('/result', [MatchController::class, 'saveResultSlack']);
            Route::post('/initiate', [MatchController::class, 'initiate']);
        });

        Route::get('/stats', [StatsController::class, 'getResult']);
        Route::post('/interaction', [InteractionController::class, 'handle']);
    });
});

Route::group(['prefix' => 'match'], function () {
    Route::post('/', [MatchController::class, 'createCustom']);
    Route::put('/result', [MatchController::class, 'saveResult']);
    Route::post('/result', [MatchController::class, 'saveResult']);
    Route::delete('/results/{event}', [MatchController::class, 'deleteResult']);
});

Route::put('/players/{playerId}', [PlayerController::class, 'updateOrCreate']);
