<?php

namespace App\Console\Commands;

use App\Models\USPS;
use Illuminate\Console\Command;

class USPSApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usps';

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
        // For city look up.
        // $response = USPS::cityLookUp(USPS::dummyCityLookUp("94556"));
        // dd($response);

        // For Zip code look up.
        // $response = USPS::zipCodeLookUp(USPS::dummyZipCodeLookUp("Suite 6100", "185 Berry Street", "San Francisco", "CA"));
        // dd($response);


        // for address validation
        $response = USPS::validateAddress(USPS::dummyAddressPayload(1, "Suite 6100", "185 Berry Street", "San Francisco", "CA", "94556"));
        dd($response);
    }
}
