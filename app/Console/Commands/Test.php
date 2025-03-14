<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	   //\App\Models\User::all()->each(function (\App\Models\User $user) {
          // event(new \App\Events\MessageReceived($user));
        //});
	$user = \App\Models\User::find(2);
        // event(new \App\Events\MessageReceived($user));
    }
}
