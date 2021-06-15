<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class system_settings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('system_settings')->insert([
            'id' => 1,
            'setting_key' => 'USD_INR',
            'setting_value' => '73.6',
            'created_at' => now(),
            'updated_at' => now()
        ],[
            'id' => 2,
            'setting_key' => 'trade_coin',
            'setting_value' => 'DOGE',
            'created_at' => now(),
            'updated_at' => now()
        ],[
            'id' => 3,
            'setting_key' => 'BTC_UP',
            'setting_value' => '2',
            'created_at' => now(),
            'updated_at' => now()
        ],[
            'id' => 3,
            'setting_key' => 'BTC_DOWN',
            'setting_value' => '-2',
            'created_at' => now(),
            'updated_at' => now()
        ],[
            'id' => 4,
            'setting_key' => 'coins',
            'setting_value' => '["XRP","DOGE","BAT","BTC","XLM","DENT","NEO","ETH","ADA","LTC"]',
            'created_at' => now(),
            'updated_at' => now()
        ],[
            'id' => 5,
            'setting_key' => 'bitbns_tic',
            'setting_value' => '{}',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
