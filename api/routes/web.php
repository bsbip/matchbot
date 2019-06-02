<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */

Route::group(['prefix' => 'api'], function () {
    Route::group([
        'prefix' => '',
        'middleware' => 'auth.token.api',
    ], function () {
        Route::get('/events/results/{page?}/{limit?}', [
            'uses' => 'MatchController@getEventResults',
        ]);
        Route::get('/events/{statusType?}', [
            'uses' => 'MatchController@getEvents',
        ]);
        Route::get('/users/slack', [
            'uses' => 'MatchController@getUserList',
        ]);
        Route::post('/match', [
            'uses' => 'MatchController@createCustom',
        ]);
        Route::post('/match/result/{update?}', [
            'uses' => 'MatchController@saveResult',
        ]);
        Route::delete('/match/result/{id}', [
            'uses' => 'MatchController@deleteResult',
        ]);
        // Stats
        Route::group([
            'prefix' => 'stats',
        ], function () {
            Route::get('/total/{period?}/{orderBy?}/{orderDirection?}', [
                'uses' => 'StatsController@getTotalStats',
            ]);
            Route::get('/players/{limit?}', [
                'uses' => 'StatsController@getPlayerStats',
            ]);
        });

        // Standings
        Route::group([
            'prefix' => 'standings',
        ], function () {
            Route::get('/duo/{period?}/{sort?}', [
                'uses' => 'StatsController@getDuoStats',
            ]);
        });

        // Player
        Route::group([
            'prefix' => 'player',
        ], function () {
            Route::post('/', [
                'uses' => 'PlayerController@addPlayer',
            ]);
            Route::put('/', [
                'uses' => 'PlayerController@updatePlayer',
            ]);
        });
    });

    Route::group([
        'prefix' => 'slack',
        'middleware' => 'auth.token.slack',
    ], function () {
        Route::post('/match', [
            'uses' => 'MatchController@create',
        ]);
        Route::post('/match/result', [
            'uses' => 'MatchController@saveResultSlack',
        ]);
        Route::get('/stats', [
            'uses' => 'StatsController@getResult',
        ]);
    });

    Route::any('/{path?}', function () {
        return new JsonResponse([
            'msg' => 'Not a valid API call.',
        ], Response::HTTP_BAD_REQUEST);
    });
});

Route::group([
    'prefix' => '',
    'middleware' => 'auth.token.app',
], function () {
    Route::any('{path?}', function () {
        return new JsonResponse('Not found.', Response::HTTP_NOT_FOUND);
    })->where('path', '.+');
});
