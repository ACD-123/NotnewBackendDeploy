<?php

namespace App\Console\Commands;

use App\Models\Fedex;
use Illuminate\Console\Command;

class FedexApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fedex';

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
        // getting token and essentials .
        $token = Fedex::authorize();

        if($token['access_token'] && array_key_exists('access_token', $token)) {
           $response = Fedex::validateShipment($token['access_token'], Fedex::dummyPayload());
           dd($response);
        }
    }
}
