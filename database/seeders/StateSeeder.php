<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('states')->insert( [
            [ 'code'=> 'AK', 'name'=> 'Alaska'],
            [ 'code'=> 'TX', 'name'=> 'Texas'],
            [ 'code'=> 'AL', 'name'=> 'Alabama'],
            [ 'code'=> 'AR', 'name'=> 'Arkansas'],
            [ 'code'=> 'AZ', 'name'=> 'Arizona'],
            [ 'code'=> 'CA', 'name'=> 'California'],
            [ 'code'=> 'CO', 'name'=> 'Colorado'],
            [ 'code'=> 'CT', 'name'=> 'Connecticut'],
            [ 'code'=> 'DC', 'name'=> 'DistrictofColumbia'],
            [ 'code'=> 'DE', 'name'=> 'Delaware'],
            [ 'code'=> 'FL', 'name'=> 'Florida'],
            [ 'code'=> 'GA', 'name'=> 'Georgia'],
            [ 'code'=> 'HI', 'name'=> 'Hawaii'],
            [ 'code'=> 'IA', 'name'=> 'Iowa'],
            [ 'code'=> 'ID', 'name'=> 'Idaho'],
            [ 'code'=> 'IL', 'name'=> 'Illinois'],
            [ 'code'=> 'IN', 'name'=> 'Indiana'],
            [ 'code'=> 'KS', 'name'=> 'Kansas'],
            [ 'code'=> 'KY', 'name'=> 'Kentucky'],
            [ 'code'=> 'LA', 'name'=> 'Louisiana'],
            [ 'code'=> 'MA', 'name'=> 'Massachusetts'],
            [ 'code'=> 'MD', 'name'=> 'Maryland'],
            [ 'code'=> 'ME', 'name'=> 'Maine'],
            [ 'code'=> 'MI', 'name'=> 'Michigan'],
            [ 'code'=> 'MN', 'name'=> 'Minnesota'],
            [ 'code'=> 'MO', 'name'=> 'Missouri'],
            [ 'code'=> 'MS', 'name'=> 'Mississippi'],
            [ 'code'=> 'MT', 'name'=> 'Montana'],
            [ 'code'=> 'NC', 'name'=> 'NorthCarolina'],
            [ 'code'=> 'ND', 'name'=> 'NorthDakota'],
            [ 'code'=> 'NE', 'name'=> 'Nebraska'],
            [ 'code'=> 'NH', 'name'=> 'NewHampshire'],
            [ 'code'=> 'NJ', 'name'=> 'NewJersey'],
            [ 'code'=> 'NM', 'name'=> 'NewMexico'],
            [ 'code'=> 'NV', 'name'=> 'Nevada'],
            [ 'code'=> 'NY', 'name'=> 'NewYork'],
            [ 'code'=> 'OH', 'name'=> 'Ohio'],
            [ 'code'=> 'OK', 'name'=> 'Oklahoma'],
            [ 'code'=> 'OR', 'name'=> 'Oregon'],
            [ 'code'=> 'PA', 'name'=> 'Pennsylvania'],
            [ 'code'=> 'RI', 'name'=> 'RhodeIsland'],
            [ 'code'=> 'SC', 'name'=> 'SouthCarolina'],
            [ 'code'=> 'SD', 'name'=> 'SouthDakota'],
            [ 'code'=> 'TN', 'name'=> 'Tennessee'],
            [ 'code'=> 'TX', 'name'=> 'Texas'],
            [ 'code'=> 'UT', 'name'=> 'Utah'],
            [ 'code'=> 'VA', 'name'=> 'Virginia'],
            [ 'code'=> 'VT', 'name'=> 'Vermont'],
            [ 'code'=> 'WA', 'name'=> 'Washington'],
            [ 'code'=> 'WI', 'name'=> 'Wisconsin'],
            [ 'code'=> 'WV', 'name'=> 'WestVirginia'],
            [ 'code'=> 'WY', 'name'=> 'Wyoming']
            ]
        );
    }
}
