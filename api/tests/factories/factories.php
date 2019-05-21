<?php

use App\Team;
use App\Player;
use App\User;
use App\Event;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory('App\User', [
	'name' => $faker->name,
	'email' => $faker->safeEmail,
	'password' => bcrypt('test1234'),
	'remember_token' => str_random(10),
]);

$factory('App\Team', [
	'name' => $faker->name,
	'status' => 1
]);

$factory('App\Player', [
	'name' => $faker->name,
	'user_id' => 'factory:App\User',
	'status' => 1,
	'default' => 1,
	'username' => $faker->name
]);

$factory('App\TeamPlayer', function($faker) {
	$player = Player::inRandomOrder()->first();
	$team = Team::inRandomOrder()->first();
	return [
		'player_id' => isset($player) ? $player->id : 'factory:App\Player',
		'team_id' => isset($team) ? $team->id : 'factory:App\Team',
		'status' => 1
	];
});

$factory('App\Event', [
	'name' => $faker->name,
	'start' => $faker->dateTime,
	'end' => $faker->dateTime,
	'status' => 1
]);

$factory('App\EventTeam', function($faker) {
	$event = Event::inRandomOrder()->first();
	$team = Team::inRandomOrder()->first();
	return [
		'event_id' => isset($event) ? $event->id : 'factory:App\Event',
		'team_id' => isset($team) ? $team->id : 'factory:App\Team'
	];
});

$factory('App\EventPlayer', function($faker) {
	$event = Event::inRandomOrder()->first();
	return [
		'event_id' => isset($event) ? $event->id : 'factory:App\Event',
		'player_id' => 'factory:App\Player',
		'number' => $faker->numberBetween(0,4)
	];
});

$factory('App\Result', [
	'event_id' => 'factory:App\Event',
	'team_id' => 'factory:App\Team',
	'score' => $faker->numberBetween(0,10),
	'crawl_score' => $faker->numberBetween(0,4)
]);