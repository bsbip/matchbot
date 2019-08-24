<?php

use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // seed
        TestDummy::times(100)->create('App\Result');
        TestDummy::times(200)->create('App\EventTeam');
        TestDummy::times(400)->create('App\EventPlayer');
        TestDummy::times(200)->create('App\TeamPlayer');
    }
}
