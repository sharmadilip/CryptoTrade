<?php

namespace App\Console\Commands;

use App\CryptoTrade;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CustomeCoin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:trade_coin';
    protected $btc_price=array();
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command to Fetch current data by websocket';

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
    {   $api_trade=new CryptoTrade();
        $binace_api=$api_trade->Bitbns_api->get_binance_api();
        $trade_coin=$api_trade->get_setting_value('trade_coin');
        $symbol=$trade_coin."BUSD";
        $binace_api->trades([$symbol], function($api, $symbol, $trades) use($api_trade) {

            $timestamp=Carbon::now()->timestamp;

                $this->btc_price[$timestamp]=$trades['price'];

            $values=Carbon::now()->subSeconds(20)->timestamp;

            if(isset($this->btc_price[$values]))
            {

                $last=$this->btc_price[$values];
               // echo "in Loop ".$last.'--'.$trades['price'];
                print_r($this->btc_price);
                $get_per_change=$api_trade->get_percentage_change($trades['price'],$last);
                if($get_per_change > 1)
                {
                    $api_trade->buy_trade_coin_current();
                    Log::info("Buy Order By websocket");
                }
                else if($get_per_change < -1){
                    $api_trade->sell_trade_coin_current();
                    Log::info("Sell Order By websocket");
                }

                $this->btc_price=array();
            }

        });

    }
}
