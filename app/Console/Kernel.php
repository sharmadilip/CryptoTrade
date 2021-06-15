<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call('App\Http\Controllers\HomeController@run_in_every_minute')->everyMinute();
        $schedule->call('App\Http\Controllers\HomeController@run_every_two_minutes')->everyTwoMinutes();
        $schedule->call('App\Http\Controllers\HomeController@run_on_every_five_minute')->everyThreeMinutes();
        $schedule->call('App\Http\Controllers\HomeController@run_every_fifty_minutes')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
