<?php

namespace App\Console;

use App\Models\City;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\CaptureFunds;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\CaptureFunds::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('capture:funds')->daily();
        // $schedule->command('expire:ads')->daily();
        
        // $schedule->call(function () {
        //     DB::insert('INSERT into cities (name) VALUES (?)', ['1']);
        // })->everyMinute();
        // $schedule->command('queue:work --stop-when-empty')->everyMinuSe(); 
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
