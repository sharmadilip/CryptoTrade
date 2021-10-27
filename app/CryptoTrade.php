<?php

namespace App;
use App\BitbnsApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Hamcrest\Arrays\IsArray;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

/**
 * Class CryptoTrade
 * @package App
 */
class CryptoTrade
{   public $Bitbns_api;
    public function __construct()
    {
        $this->Bitbns_api=new BitbnsApi();

    }

    /**
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     */
    public function fetch_market_by_coin($symbol)
    {
        $data = $this->Bitbns_api->get_ticker();
        return $data[$symbol."/INR"]["info"];

    }

    /**
     * @param $key
     * @return mixed
     */
    public function get_setting_value($key)
{
    $key_value=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>$key))->first();
    return $key_value->setting_value;
}
//--------get streatgey data----------------------
public function get_strategy($key)
{
    $strategy=DB::table("strategy_data")->where('strategy_key','=',$key)->first();
    return $strategy;
}
    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function update_setting_value($key, $value)
    {
        DB::table("system_settings")->updateOrInsert(array('setting_key'=>$key),array('setting_key'=>$key,'setting_value'=>$value));
        return "true";
    }
    /**
     * @note Get value of coin from binance ticker form
     */
    public function binance_single_tic_data()
    {
        $usd_rate=self::get_setting_value("USD_INR");
        $symbol=self::get_setting_value("trade_coin");
        $binace = $this->Bitbns_api->get_binance_api();
        $binace->ticker($this->trade_symbol."USDT", function($api, $symbol, $ticker) {
            print_r($ticker);
        });
    }

    /**
     * @note Get coins value from kucoin
     * @param $coin
     * @param $usd_rate
     * @return float|int
     */
    public function get_coin_value_binance($coin, $usd_rate)
    {
        $symbol=$coin;

        // $binance=  $bitbns->get_binance_api();
        //$usd_rate=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>'USD_INR'))->first();
        //  $price = $binance->price($symbol."USDT");
        $price=$this->Bitbns_api->get_kucoin_price_symbol_usdt($symbol);
        $inr_price=$price;
        return $inr_price;
    }

//-----------------------------currency update-----------------------------------------

    /**
     * @note Update USD value in database
     * @return float
     */
    public function update_usd_to_db()
    {

        $price=round($this->Bitbns_api->update_usd_price(),2);
        DB::table("system_settings")->updateOrInsert(array('setting_key'=>"USD_INR"),array('setting_key'=>"USD_INR","setting_value"=>$price,'created_at'=>Carbon::now()->toDateTimeString(),'updated_at'=>Carbon::now()->toDateTimeString()));
        return $price;
    }

    /**
     * @note Get binance price in usdt
     * @return array
     * @throws \Exception
     */
    public function get_binance_prices_usdt()
    {

        $api=$this->Bitbns_api->get_binance_api();
        $price = $api->prices();

        $binance_price=array_filter($price, function($k) {
            return strpos($k,"USDT") != 0;
        }, ARRAY_FILTER_USE_KEY);
        return $binance_price;
    }

    /**
     * @note Cancle Order
     * @param $symbol
     * @param $order_id
     * @return mixed
     */
    function cancle_order($symbol, $order_id)
    {

        $payload['entry_id']=$order_id;

        $cancel=json_decode($this->Bitbns_api->CancleOrder($symbol,$payload),true);
        if($cancel['code']==200)
        {    self::alert_telegram_chat("Bid has canceled $symbol :".$payload['entry_id']);
            DB::table("order_table")->where("order_id",$order_id)->delete();
        }
        else{
            Log::error("Error in order Place:-".json_encode($cancel));
        }
        return $order_id;
    }
    /**
     * @note Cancle Order with no alert
     * @param $symbol
     * @param $order_id
     * @return mixed
     */
    function cancle_order_no_alert($symbol, $order_id)
    {

        $payload['entry_id']=$order_id;

        $cancel=json_decode($this->Bitbns_api->CancleOrder($symbol,$payload),true);

        if($cancel["status"]==1)
        {
            DB::table("order_table")->where("order_id",$order_id)->delete();
        }
        else{
            Log::error("Error in order Place:-".json_encode($cancel));
        }
        return $order_id;
    }
//---------------create buy order with function--------------------------

    /**
     * @note Create buy order
     * @param $symbol
     * @param $data
     * @return mixed
     */
    function create_buy_order_bitbns($symbol, $data)
    {
        $order_buy=self::should_order_or_not($symbol,"0");
        $strategy=self::get_setting_value('strategy_value');
        if($order_buy==true) {
            $order_data = json_decode($this->Bitbns_api->create_order($symbol, $data), true);

            if ($order_data['status'] == "1") {
                $order_id = floatval($order_data['id']);
                $quantity = floatval($data['quantity']);
                $rate = $data['rate'];
                DB::table("order_table")->insert(array("order_id" => $order_id, "quantity" => $quantity, "price" => $rate, "coin" => $symbol, "order_status" => 0, "order_type" => 0,"strategy"=>$strategy, "created_at" => Carbon::now()->toDateTimeString(), "updated_at" => Carbon::now()->toDateTimeString()));
                self::update_coin_balance();
                self::alert_telegram_chat("A buy order of $symbol Quantity:- $quantity at:-$rate has created");
                Log::emergency("coin order has placed successfully");
                return $order_data['id'];
            } else {
                Log::error("Error in order Place:-" . json_encode($order_data));
            }
        }
        else{
            Log::notice("Order has already done in last 20 minutes");
        }
        return "true";
    }
//-------------validate this order should less then in term % to last order------------------
    function last_order_price_validation($current_price,$percentage_change,$order_type)
    {
        $trade_coin=self::get_setting_value("trade_coin");
        $strategy=self::get_setting_value("strategy_value");
        $get_price=DB::table('order_table')->select("price")->where(array('strategy'=>$strategy,'coin'=>$trade_coin,'order_status'=>2,"order_type"=>$order_type))->orderBy('id',"desc")->first();
        if(isset($get_price))
        {
            $current_change=self::get_percentage_change($current_price,$get_price->price);
            //echo $current_change."--".$percentage_change;
            if($strategy==2||$strategy==0)
            {
                if (round($current_change) < round($percentage_change)) {
                    return "true";
                } else {
                    return "false";
                }
            }
            else {
                if (round($current_change) > round($percentage_change)) {
                    return "true";
                } else {
                    return "false";
                }
            }
        }
        else{
            return "true";
        }


    }
//-----------------validate sell order for current price sell-------------------------------
    function last_order_price_sell_validation($current_price,$percentage_change,$order_type)
    {
        $trade_coin=self::get_setting_value("trade_coin");
        $strategy=self::get_setting_value("strategy_value");
        $coin_blance=DB::table("coin_blance")->select('quantity')->where('coin_name',$trade_coin)->first();
        
        $coin_slot=DB::table("coin_setting")->select('slot_value')->where('coin_name',$trade_coin)->first();
        if(!isset($coin_blance->quantity))
        {
            $row_limit=1;
        }
        else{
        
        $row_limit=round($coin_blance->quantity/$coin_slot->slot_value);
        }
        $get_price_data=DB::table('order_table')->select("price","id")->where(array('strategy'=>$strategy,'coin'=>$trade_coin,'order_status'=>2,"order_type"=>0,"order_coins_status"=>0))->orderBy('id',"desc")->limit($row_limit)->get();
         $status="false";
        foreach($get_price_data as $get_price)
        {
            $current_change=self::get_percentage_change($current_price,$get_price->price);
            if($current_change >= $percentage_change&&$current_price > $get_price->price)
            {
                $status="true";
                DB::table("order_table")->where('id','=',$get_price->id)->limit(1)->update(array('order_coins_status'=>1));
                break;
            }
            //echo $current_change."--".$percentage_change;

        }
        return $status;
    }
    /**
     * @note Check should place order again or not(Main conditinal Logic is here )
     * @param $symbol
     * @param $type
     * @return bool
     */
    function should_order_or_not($symbol, $type)
    {   $order_again_time=20;
        $determine_hourly_up=4;
        $minute_minute=Carbon::now()->subMinutes($order_again_time)->toDateTimeString();
        $hourly_change=Carbon::now()->subHour($determine_hourly_up)->toDateTimeString();
        $status=true;
        //-------------------old data order status 2 successfull order-----------------------------------------
        $order_data=DB::table("order_table")->select('order_id')->where('created_at',">=",$minute_minute)->where(array("coin"=>$symbol,"order_type"=>$type,"order_status"=>2))->orderBy('id',"desc")->first();
        if(isset($order_data->order_id))
        {$status=false;}
        if($status==true) {
            $hourly_data = DB::table("price_data")->select('data_json')->where('created_at', "<", $hourly_change)->orderBy('id', "desc")->first();
            $current_data = DB::table("price_data")->select('data_json')->orderBy('id', "desc")->first();
            $previous_price=json_decode($hourly_data->data_json,true);
            $current_price=json_decode($current_data->data_json,true);
            $changePercent=((floatval($current_price[$symbol])-floatval($previous_price[$symbol]))/floatval($previous_price[$symbol]))*100;
            $chnage_percentage=self::get_setting_value('change_two_hours');
            //------------25% deinfe for not order again in 2 hr---------------
            if($changePercent > floatval($chnage_percentage))
            {
                $status=false;
            }
        }
        return $status;

    }
//---------------create sell order with function--------------------------

    /**
     * @note Create sell order
     * @param $symbol
     * @param $data
     * @return mixed
     */
    function create_sell_order_bitbns($symbol, $data)
    {

        $order_sell=self::should_order_or_not($symbol,"1");
        if($order_sell==true) {
            $order_data = json_decode($this->Bitbns_api->sell_order($symbol, $data), true);
            if ($order_data['status'] == "1") {
                $strategy=self::get_setting_value('strategy_value');
                $order_id = $order_data['id'];
                $quantity = $data['quantity'];
                $rate = $data['rate'];
                DB::table("order_table")->insert(array("order_id" => $order_id, "quantity" => $quantity, "price" => $rate, "coin" => $symbol, "order_status" => 0, "order_type" => 1,"strategy"=>$strategy, "created_at" => Carbon::now()->toDateTimeString(), "updated_at" => Carbon::now()->toDateTimeString()));
                self::update_coin_balance();
                self::alert_telegram_chat("A sell order of $symbol Quantity:- $quantity at:-$rate has created");
                Log::emergency("coin sell order has placed successfully");
                return $order_data['id'];
            } else {
                Log::error("Error in order Place:-" . json_encode($order_data));

            }
        }
        else{
            Log::notice("already sell done");
        }
        return false;
    }



    /**
     * @note Fetch all bids
     * @param $symbol
     * @param $data
     * @return mixed
     */
    function get_all_open_order($symbol, $data)
    {

        $open_order=json_decode($this->Bitbns_api->listOpenOrders($symbol,json_encode($data)));
        return $open_order;
    }
    /**
     * @note Check if bid already exit-------------------------
     * @param $symbol
     * @param $price
     * @param $type
     * @return bool
     */
    function check_bid_already_exist($symbol, $price, $type)
    {

        $get_order=DB::table("order_table")->select(array("order_id","price"))->where(array("coin"=>$symbol,"order_status"=>0,"order_type"=>$type))->get();
          if(isset($get_order)) {
              foreach ($get_order as $my_data) {
                  if ($my_data->price != $price) {
                      self::cancle_order($symbol, $my_data->order_id);

                  }

              }
          }
        return false;
    }

    /**
     * @note Cancel all bids
     * @param $symbol
     */
    function cancel_all_bid($symbol)
    {

        $get_order=DB::table("order_table")->select(array("order_id","coin"))->where(array("order_status"=>0,"coin"=>$symbol))->get();
        if(isset($get_order)) {
            foreach ($get_order as $my_data) {
                    self::cancle_order_no_alert($my_data->coin, $my_data->order_id);
            }
        }
        return true;
    }
    /**
     * @note Sys order status of the pending order
     */
    function sys_order_status()
    {

        $get_order=DB::table("order_table")->where('order_status',0)->get();
        foreach ($get_order as $my_order)
        {    $symbol=$my_order->coin;
            $payload['symbol']=$symbol;
            $payload['entry_id']=$my_order->order_id;
            $status_data=json_decode($this->Bitbns_api->orderStatus($symbol,$payload),true);
            $status=$status_data['data'][0]['status'];
            if($status!='0')
            { if($status=="-1"){
                $status=1;
            }

                DB::table('order_table')->where('order_id',$my_order->order_id)->update(array('order_status'=>$status));
            }

        }

    }


    /**
     * @note Will fetch all coin balance from bitbns
     * @return string
     */
    function update_coin_balance()
    {

        $body['page']=0;
        $data = json_decode($this->Bitbns_api->AllCoinBalance("EVERYTHING",$body),true);
        DB::table("coin_blance")->delete();
        foreach ($data['data'] as $key=>$value)
        {
            if($value!=0)
            { $symbol=str_replace("availableorder","",$key);
                $quantity=floatval($value);

                DB::table("coin_blance")->insert(array('coin_name' => "$symbol", "quantity" => $quantity, 'created_at' => Carbon::now()->toDateTimeString(), 'updated_at' => Carbon::now()->toDateTimeString()));


            }

        }

        return "Successfully Sync";

    }


    /**
     * @note Get all orders of coins---------------
     * @return string
     */
    function sys_order_book_by_coin()
    {

        $body['page']=0;
        $body['since']=Carbon::today();
        $get_coins=DB::table("coin_setting")->select("coin_name")->get();
        $run_the_bot=self::get_setting_value('run_the_bot');
        $trade_coin=self::get_setting_value('Trade_Coin');
        $strategy=self::get_setting_value('strategy_value');
        foreach ($get_coins as $data_sys) {
            $list_sys = json_decode($this->Bitbns_api->order_list_data($data_sys->coin_name, $body),true);
            if(isset($list_sys['data']))
            {
            foreach ($list_sys['data'] as $order_row)
            {
                $type=1;
                if($data_sys->coin_name=="XRP"||$data_sys->coin_name=="XLM"||$data_sys->coin_name=="CAS"||$data_sys->coin_name=="BAT")
                {
                    $quatity=$order_row['crypto']/100;
                }
                else{
                    $quatity=$order_row['crypto'];
                }
                if($order_row['typeI']==7)
                {
                    $type=0;
                }
                //---------if current coin is trade coin-----------
                if($trade_coin==$data_sys->coin_name&&$type==1&&$run_the_bot==1)
                {
                 $check_order_exit=DB::table('order_table')->select("order_id")->where(array("order_id"=>$order_row['id']))->first();
                 if(isset($check_order_exit->order_id))
                 {
                    DB::table("order_table")->updateOrInsert(array("order_id"=>$order_row['id']),array("order_id"=>$order_row['id'],"quantity"=>$quatity,"price"=>round($order_row['rate'],6),"coin"=>$data_sys->coin_name,"order_status"=>2,"order_type"=>$type));
                 }
                 else{
                    DB::table("order_table")->updateOrInsert(array("order_id"=>$order_row['id']),array("order_id"=>$order_row['id'],"quantity"=>$quatity,"price"=>round($order_row['rate'],6),"coin"=>$data_sys->coin_name,"order_status"=>2,"order_type"=>$type,"strategy"=>$strategy,"created_at"=>Carbon::now()->toDateTimeString(),"updated_at"=>Carbon::now()->toDateTimeString()));
                 }

                }
                //---------update coin order book as ordinary order book------------------
                else{

                $check_order_exit=DB::table('order_table')->select("order_id")->where(array("order_id"=>$order_row['id']))->first();
                if(isset($check_order_exit->order_id))
                {
                    DB::table("order_table")->updateOrInsert(array("order_id"=>$order_row['id']),array("order_id"=>$order_row['id'],"quantity"=>$quatity,"price"=>round($order_row['rate'],6),"coin"=>$data_sys->coin_name,"order_status"=>2,"order_type"=>$type));
                }
                else{
                    DB::table("order_table")->updateOrInsert(array("order_id"=>$order_row['id']),array("order_id"=>$order_row['id'],"quantity"=>$quatity,"price"=>round($order_row['rate'],6),"coin"=>$data_sys->coin_name,"order_status"=>2,"order_type"=>$type,"created_at"=>Carbon::now()->toDateTimeString(),"updated_at"=>Carbon::now()->toDateTimeString()));
                }
            }

            }
            }
        }
        return "Updated Sucessfully";
        
    }


    /**
     * @return string
     */
    function sys_order_book_by_coin_today()
    {

        $body['page']=0;
        $body['since']=Carbon::today();
        $get_coins=DB::table("coin_setting")->select("coin_name")->get();

        foreach ($get_coins as $data_sys) {
            $list_sys = json_decode($this->Bitbns_api->order_list_data($data_sys->coin_name, $body),true);
            foreach ($list_sys['data'] as $order_row)
            {
                $type=1;
                if($data_sys->coin_name=="XRP"||$data_sys->coin_name=="XLM")
                {
                    $quatity=$order_row['crypto']/100;
                }
                else{
                    $quatity=$order_row['crypto'];
                }
                if($order_row['typeI']==7)
                {
                    $type=0;
                }
                DB::table("order_table")->updateOrInsert(array("order_id"=>$order_row['id']),array("order_id"=>$order_row['id'],"quantity"=>$quatity,"price"=>round($order_row['rate'],6),"coin"=>$data_sys->coin_name,"order_status"=>2,"order_type"=>$type,"created_at"=>Carbon::now()->toDateTimeString(),"updated_at"=>Carbon::now()->toDateTimeString()));
            }
        }
        return "Updated Sucessfully";
    }
    /**
     * @note Get percentage changes of currency
     * @param $current_price
     * @param $last_price
     * @return float
     */
    function get_percentage_change($current_price, $last_price)
    {   //echo $current_price;

        $diffr=$current_price-$last_price;
        $percent_change=round(($diffr/$last_price)*100,2);
        return $percent_change;
    }

//---------------------this function will cancel all orders------------------------------

    /**
     * @note Cancle All previous bids
     * @param $symbol
     */
    function cancel_all_bid_current_coin($symbol)
    {

        $formatted_date = Carbon::now()->subMinutes(2)->toDateTimeString();
        $get_data=DB::table("order_table")->where("order_status","0")->where('created_at','<=',$formatted_date)->get();
        foreach ($get_data as $my_data)
        {
            self::cancle_order($symbol, $my_data->order_id);
            //self::alert_telegram_chat("All Bid has canceled");
        }



    }
//--------------------function will alert for the price---------------------------------

    /**
     * @note Main alert Genrating  Function in 2 minutes
     */
    function binance_price_rising_alert()
    {


        $minute_minute=Carbon::now()->subMinutes("3")->toDateTimeString();
        //-------------------old data-----------------------------------------
        $bitbns_old_data=DB::table("price_data")->select('data_json')->orderBy('id',"desc")->where('created_at',"<",$minute_minute)->first();

        if(isset($bitbns_old_data->data_json)) {
            $bitbns_old_price = json_decode($bitbns_old_data->data_json, true);
        }
        else{
            $bitbns_old_price=array();
        }
        //--------------get ddata from apis------------
        $new_data_row=DB::table("price_data")->select('data_json')->orderBy('id',"desc")->first();
        $new_data=json_decode($new_data_row->data_json,true);

        //-----------------end api data------------------
        $btc_up=self::get_setting_value('BTC_UP');
        $btc_down=self::get_setting_value('BTC_DOWN');
        $allow_coin_data=self::get_setting_value('coins');
        $allow_coin=json_decode($allow_coin_data,true);
        //----------------------------end coins setting data----------------------
        $up_coin='Coins Are up ';
        $down_coins="Coin are Down ";
        $up_status=0;
        $down_status=0;
        foreach ($allow_coin as $symbol)
        {   if(isset($new_data[$symbol]))
        {$bitbns_price=$new_data[$symbol];}

            if(isset($bitbns_price)) {
                if (isset($bitbns_old_price[$symbol])) {
                    $percentage_change = round(self::get_percentage_change($bitbns_price, $bitbns_old_price[$symbol]), 2);
                    echo $percentage_change . "--";
                    if ($percentage_change > $btc_up) {
                        $up_status = 1;
                        $up_coin .= $symbol . ":{$percentage_change}%,";
                    } else if ($percentage_change < $btc_down) {
                        $down_coins .= $symbol . ":{$percentage_change}%,";
                        $down_status = 1;
                    }
                }
            }
        }

        if($up_status==1)
        {
            self::alert_telegram_chat($up_coin);
        }
        else if($down_status==1)
        {   self::alert_telegram_chat($down_coins);
        }
        //-----------insert data ------------------
    }


    /**
     *  @note insert price in database every minute
     */
    function insert_price_to_db_every_minute()
    {
        $time=Carbon::now()->toDateTimeString();
        $binance_data=$this->Bitbns_api->get_binance_prices_usdt();
        $kuCoin=$this->Bitbns_api->get_kucoin_price_usdt();
        $allow_coin_data=self::get_setting_value('coins');
        $allow_coin=json_decode($allow_coin_data,true);
        $for_insert_db=array();
        foreach ($allow_coin as $symbol) {
            if (isset($binance_data[$symbol . "USDT"])) {
                $bitbns_price = $binance_data[$symbol . "USDT"];
            } else if (isset($kuCoin[$symbol])) {
                $bitbns_price = $kuCoin[$symbol];
            }

            if (isset($bitbns_price)) {
                $for_insert_db[$symbol] = $bitbns_price;
            }
        }
        $data_insert['data_json']=json_encode($for_insert_db);
        $data_insert['created_at']=$time;
        //print_r($data_insert);
        DB::table("price_data")->insert($data_insert);


    }


    /**
     *  @note get price changes in 3 min
     * @param $symbol
     * @param $current_price
     * @param $usd_rate
     * @return float|int
     * @throws \Exception
     */
    function get_price_change_in_five($symbol, $current_price, $usd_rate)
    {

        $usd_rate=round($usd_rate,2);
        $formatted_date = round(Carbon::now()->subMinutes(5)->timestamp* 1000.0);
        $current_time=round(Carbon::now()->timestamp* 1000.0);
        $binance=$this->Bitbns_api->get_binance_api();
        $get_candel=  $binance->candlesticks($symbol."USDT", "5m",1,$formatted_date,$current_time);
        $data_array=array();

        foreach ($get_candel as $key=> $data)
        {
            $data_array[]=$data['open'];
        }
        if(!empty($data_array)) {
            $percent_change_pos = self::get_percentage_change($current_price, $data_array[0]);
        }
        else{
            $percent_change_pos=0;
        }

        return $percent_change_pos;
    }
     /* Getiing btc value in 15 minutes from binance exchange */
    function get_price_change_in_15($symbol, $current_price, $usd_rate)
    {

        $usd_rate=round($usd_rate,2);
        $formatted_date = round(Carbon::now()->subMinutes(15)->timestamp* 1000.0);
        $current_time=round(Carbon::now()->timestamp* 1000.0);
        $binance=$this->Bitbns_api->get_binance_api();
        $get_candel=  $binance->candlesticks($symbol."USDT", "15m",1,$formatted_date,$current_time);
        $data_array=array();

        foreach ($get_candel as $key=> $data)
        {
            $data_array[]=$data['open'];
        }
        if(!empty($data_array)) {
            $percent_change_pos = self::get_percentage_change($current_price, $data_array[0]);
        }
        else{
            $percent_change_pos=0;
        }

        return $percent_change_pos;
    }
    /**
     * @note Get price changes in every 5 minutes
     * @param $symbol
     * @param $current_price
     * @param $usd_rate
     * @return float|int
     */
    function get_price_change_in_database($symbol, $current_price, $usd_rate)
    {

        $usd_rate=round($usd_rate,2);
        $formatted_date = Carbon::now()->subSeconds(63)->toDateTimeString();
        $previos_data=DB::table("price_data")->select("data_json")->where('created_at',"<=",$formatted_date)->orderBy("id","desc")->first();
        $get_data_p=json_decode($previos_data->data_json,true);
        $data_array=$get_data_p[$symbol];

        if(!empty($data_array)) {
            //echo $current_price.'--'.$data_array.'--'.$get_data_p[$symbol];
            $percent_change_pos = self::get_percentage_change($current_price, $data_array);
        }
        else{
            $percent_change_pos=0;
        }

        return $percent_change_pos;
    }
//-----------------get increasing price of data from last 6 minutes------------------

    /**
     * @note Price change in db every minute
     * @param $symbol
     * @param $minutes
     * @return array
     */
    function get_price_change_minutes_db($symbol, $minutes)
    { $formatted_date = Carbon::now()->subMinutes($minutes)->toDateTimeString();
        $get_data=DB::table("price_data")->select('data_json')->where('created_at','>',$formatted_date)->orderBy("id","desc")->pluck("data_json");
        $return_data=array();
        foreach ($get_data as $my_data)
        {
            $data_json=json_decode($my_data,true);
            $return_data[]=$data_json[$symbol];
        }
        return $return_data;
    }

    /**
     * @note Clear data base old history of 2 days
     */
    function alert_telegram_chat($message)
    {  $responce='';
        $trade_setting_row=self::get_setting_value('telegram_token');
        $trade_chat=self::get_setting_value('telegram_chat');
        if(isset($trade_setting_row)&&isset($trade_chat)) {
            $token = $trade_setting_row;
            $data['chat_id'] = $trade_chat;
            $data['text'] = $message;
            $url = "https://api.telegram.org/{$token}/sendMessage";
            $responce = Http::withHeaders(['content-type' => 'application/json'])->post($url, $data);
        }
        return $responce;
    }

    /**
     * @note Clear data base old history of 2 days
     */
    function clear_old_price_data_entries()
    {
        $formatted_date = Carbon::now()->subDays(3)->toDateTimeString();
        DB::table("price_data")->where('created_at','<',$formatted_date)->delete();
    }
    /**
     * @note Get avg price of a coin
     * @param $symbol
     * @param $hrs
     * @return float
     */
    function get_average_price_of_all_coin($hrs)
    {
        $total_coin_array = array();
        $time_get_data = Carbon::now()->subDays($hrs)->toDateTimeString();
        $get_all_row = DB::table("price_data")->select("data_json")->where("created_at", ">", $time_get_data)->orderBy("id","DESC")->get();
        foreach ($get_all_row as $pricedata) {
            $current_data = json_decode($pricedata->data_json, true);
            foreach ($current_data as $coin => $value) {
                $total_coin_array[$coin][] = $value;
            }

        }
        $responce_array = array();

        foreach ($total_coin_array as $coin => $value_s) {
            $responce_array[$coin] = round(array_sum($value_s) / count($value_s), 5);
        }
        return $responce_array;

    }
    /**
     * @note Get trade price
     * @return array
     */
    function get_trade_price()
    {
        $path = storage_path() . "/json/price.json";
        $json = json_decode(file_get_contents($path), true);
        return $json;
    }
    function get_trade_price_by_coin($symbol)
    {
        $path = storage_path() . "/json/{$symbol}_price.json";
        $json = json_decode(file_get_contents($path), true);
        return $json;
    }
    /**
     * @note Get trade price
     * Kill Previous node js function
     */
   function to_run_kill_previos_node_hook()
   {
       exec('ps -ef | grep get_price.js',$grep_data,$result);
       print_r($grep_data);
       foreach ($grep_data as $key=>$p_data)
       {

           $my_data=explode('  ',trim(str_replace('daemon','',$p_data),"  "),2);
           $pid= (int) filter_var($my_data[0], FILTER_SANITIZE_NUMBER_INT);
           $cmd="sudo kill $pid";
           exec($cmd,$rs,$post);

       }
       shell_exec('nohup node get_price.js >/dev/null 2>&1 &');



   }
   function to_run_trade_coin_hook($trade_coin)
    {
        exec('ps -ef | grep get_price_trade.js',$grep_data,$result);
        print_r($grep_data);

        foreach ($grep_data as $key=>$p_data)
        {

            $my_data=explode('  ',trim(str_replace('daemon','',$p_data),"  "),2);
            $pid= (int) filter_var($my_data[0], FILTER_SANITIZE_NUMBER_INT);
            //  $pid=substr($matches, 0, 5);
            $cmd="kill $pid";
            exec($cmd,$rs,$post_get);

        }
        //echo 'nohup node get_price_trade.js '.$trade_coin.' >/dev/null 2>&1 &';
        shell_exec("nohup node get_price_trade.js $trade_coin >/dev/null 2>&1 &");

        return "Success";

    }

    function run_commands()
{
    exec('ps -ef | grep wesocket:get',$grep_data,$result);
    print_r($grep_data);

    foreach ($grep_data as $key=>$p_data)
    {

        $my_data=explode('  ',trim(str_replace('daemon','',$p_data),"  "),2);
        $pid= (int) filter_var($my_data[0], FILTER_SANITIZE_NUMBER_INT);
        //  $pid=substr($matches, 0, 5);
        $cmd="kill $pid";
        exec($cmd,$rs,$post_get);

    }
    shell_exec('nohup php artisan wesocket:get >/dev/null 2>&1 &');
}

//----------this function will check all the commands that neccesorry------------------
    function check_all_commands()
    {   $bot_status=self::get_setting_value('run_the_bot');
        if($bot_status==1) {
        /**    exec('ps -ef | grep wesocket:get', $grep_data, $result);
            print_r($grep_data);
            if (count($grep_data) == 2) {
                shell_exec('nohup php artisan wesocket:get >/dev/null 2>&1 &');
            }
         * */
            exec('ps -ef | grep get_price.js', $grep_data_1, $result);
            print_r($grep_data_1);
            if (count($grep_data_1) == 2) {
                shell_exec('nohup node get_price.js >/dev/null 2>&1 &');
            }
        }
        return "true";
    }
    /**
     *
     */
    function buy_trade_coin_current()
   {  $symbol =self::get_setting_value('trade_coin');
       $get_trade=self::get_trade_price();
       $trade_setting=DB::table("coin_setting")->select("*")->where("coin_name",$symbol)->first();
       $max_qt=$trade_setting->slot_value;
       $purchase_price=$get_trade['lowest_sell_bid'];
         $body['quantity']=$max_qt;
           $body['rate']=$purchase_price;
           $inr_blance=DB::table('coin_blance')->select("quantity")->where("coin_name","Money")->first();
           if($inr_blance->quantity > $max_qt*$purchase_price ) {
               self::create_buy_order_bitbns($symbol, $body);
               Log::emergency("coin order has placed successfully");
           }
           else{
               Log::emergency("Money has finished");
           }


   }

    /**
     *
     */
    function sell_trade_coin_current()
{
    $symbol =self::get_setting_value('trade_coin');
    $get_trade=self::get_trade_price();
    $sell_price=$get_trade['highest_buy_bid'];
    $trade_setting=DB::table("coin_setting")->select("*")->where("coin_name",$symbol)->first();
    $max_qt=$trade_setting->slot_value;
    $coin_blance=DB::table('coin_blance')->select("quantity")->where("coin_name",$symbol)->first();
    if($coin_blance->quantity > 0) {
        $body['quantity'] = $trade_setting->slot_value;
        $body['rate'] = $sell_price;

      self::create_sell_order_bitbns($symbol, $body);
    }
    else{
        Log::emergency("All Coin Has been sold");
    }

}

    /**
     *
     */
    function trigger_stop_loss_order($coin,$order_purchase)
   {


   }

    /**
     *
     */
    function percentage_strategy($per_change,$trade_setting,$symbol,$bitbns_tiker,$binace_price,$stratgey)
   {   $max_qt=$trade_setting->slot_value;
       //-------percentage stratgey-------------------------
       $stratgey_data=$stratgey;
       $buy_diff=$stratgey_data->percentage_up;
       $sell_dif=$stratgey_data->percentage_down;
       //---------------End Parameter------------------
       if(round($per_change,3) < $buy_diff &&$bitbns_tiker['highest_buy_bid'] > $binace_price)
       {   //---------------placing the bid not purchaing according to the function---
           $purchase_price=$bitbns_tiker['lowest_sell_bid'];
           $coin_value=DB::table("coin_blance")->select("quantity")->where("coin_name","=",$symbol)->first();
           if(isset($coin_value->quantity)&&$coin_value->quantity >= $max_qt) {
                $price_stats = self::last_order_price_validation($purchase_price, $buy_diff, 0);
           }
           else{
               $price_stats="true";
           }
           if($price_stats=="true")
           {   $body['quantity']=$max_qt;
               $body['rate']=$purchase_price;
               self::check_bid_already_exist($symbol,$purchase_price,0);
               $inr_blance=DB::table('coin_blance')->select("quantity")->where("coin_name","Money")->first();
               if($inr_blance->quantity > $max_qt*$purchase_price ) {

                   self::create_buy_order_bitbns($symbol, $body);
                   self::update_coin_balance();
                   self::sys_order_status();

               }
               else{
                   Log::emergency("Money has finished");
               }
           }

       }

       //-------------------------End place order && start sale order---------------------------------------
       else if( round($per_change,3) > $sell_dif &&$bitbns_tiker['highest_buy_bid'] > $binace_price)
       {   //---------------placing the bid not purchaing according to the function---
           $sell_price=$bitbns_tiker['highest_buy_bid'];
           $sell_status=self::last_order_price_sell_validation($sell_price,$sell_dif,1);

           if($sell_status=="true")
           {    self::check_bid_already_exist($symbol,$sell_price,1);
               $coin_blance=DB::table('coin_blance')->select("quantity")->where("coin_name",$symbol)->first();

               if(isset($coin_blance->quantity)&&$coin_blance->quantity > 0) {
                   $body['quantity'] = $trade_setting->slot_value;
                   $body['rate'] = $sell_price;

                   self::create_sell_order_bitbns($symbol, $body);
                   self::update_coin_balance();
                   self::sys_order_status();
               }
               else{
                   Log::emergency("All Coin Has been sold");
               }
           }

       }
       //-------------------binance price lower block-----------------------
       else if($binace_price > $bitbns_tiker['lowest_sell_bid'])
       {   self::buy_trade_coin_current();
           self::alert_telegram_chat("$symbol  price is  lower rate then exchange rate: ".$bitbns_tiker['lowest_sell_bid']);
       }

   }

   //----------------bulish strategy data changes----------------------------
   function bullish_strategy($per_change,$trade_setting,$symbol,$bitbns_tiker,$binace_price,$strategy)
   {   $max_qt=$trade_setting->slot_value;
       $stratgey_data=$strategy;
       $buy_diff=$stratgey_data->percentage_up;
       if(round($per_change,3) > $buy_diff &&$bitbns_tiker['highest_buy_bid'] > $binace_price)
       {   //---------------placing the bid not purchaing according to the function---
           $purchase_price=$bitbns_tiker['highest_buy_bid']+$trade_setting->add_value;
           $coin_value=DB::table("coin_blance")->select("quantity")->where("coin_name","=",$symbol)->first();
           if(isset($coin_value->quantity)&&$coin_value->quantity >= $max_qt) {
               $price_stats = self::last_order_price_validation($purchase_price, $buy_diff, 0);
           }
           else{
               $price_stats="true";
           }
           if($price_stats=="true")
           {
               $status_bid=self::check_bid_already_exist($symbol,$purchase_price,0);
               $body['quantity']=$max_qt;
               $body['rate']=$purchase_price;
               $inr_blance=DB::table('coin_blance')->select("quantity")->where("coin_name","Money")->first();
               if($inr_blance->quantity > $max_qt*$purchase_price ) {
                   self::create_buy_order_bitbns($symbol, $body);
                   self::update_coin_balance();
                   self::sys_order_status();

               }
               else{
                   Log::emergency("Money has finished");
               }
           }

       }
       //-------------------------End place order && start sale order---------------------------------------
       else if( round($per_change,3) < round($stratgey_data->percentage_up) &&$bitbns_tiker['highest_buy_bid'] > $binace_price)
       {   //---------------placing the bid not purchaing according to the function---
           $sell_price=$bitbns_tiker['lowest_sell_bid']-$trade_setting->add_value;
           $sell_status=self::last_order_price_sell_validation($sell_price,$stratgey_data->percentage_up,1);
           if($sell_status=="true")
           {   $status_bid=self::check_bid_already_exist($symbol,$sell_price,1);
               $coin_blance=DB::table('coin_blance')->select("quantity")->where("coin_name",$symbol)->first();
               if(isset($coin_blance->quantity)&&$coin_blance->quantity > 0) {
                   $body['quantity'] = $trade_setting->slot_value;
                   $body['rate'] = $sell_price;

                   self::create_sell_order_bitbns($symbol, $body);
                   self::update_coin_balance();
                   self::sys_order_status();

               }
               else{
                   Log::emergency("All Coin Has been sold");
               }
           }

       }
       //-------------------binance price lower block-----------------------
       else if($binace_price > $bitbns_tiker['lowest_sell_bid'])
       {   self::buy_trade_coin_current();
           self::alert_telegram_chat("$symbol  price is  lower rate then exchange rate: ".$bitbns_tiker['lowest_sell_bid']);
       }
   }
   
  //--------------stop loss if boat avg price is lover then price define in stratgey------------
  function stop_loss_boat()
  {
    $symbol=self::get_setting_value('Trade_Coin');
    $stratgey_key=self::get_setting_value('strategy_value');
    $stratgey_data=self::get_strategy($stratgey_key);
    $get_data=DB::table("order_table")->select("*")->where(array("order_type"=>0,"strategy"=>$stratgey_key,'coin'=>$symbol))->orderBy("id","desc")->get();
    $total_coins=DB::table("coin_blance")->select("quantity")->where("coin_name",$symbol)->first();
    $purchased_coins=0;
    $buy_price=array();
    $current_price=self::get_trade_price();
   
    if($total_coins->quantity>0)
    {
    foreach($get_data as $order_data)
    {

     $buy_price[]=$order_data->price;
     $purchased_coins=$purchased_coins+$order_data->quantity;
     if($purchased_coins>=$total_coins->quantity)
     {
         break;
     }
    }
    $average_price = array_sum($buy_price)/count($buy_price);
    $total_cahnge_percentage=self::get_percentage_change($current_price['highest_buy_bid'],$average_price);
    if($total_cahnge_percentage < -$stratgey_data->stop_loss)
    {   $body["quantity"]=$total_coins->quantity;
        $body["rate"]=$current_price['highest_buy_bid'];
        self::create_sell_order_bitbns($symbol, $body);
        return "sell Created Sucessfully-".$total_cahnge_percentage;
    }
    return "Avg Price=".$average_price." CurrentPrice=".$current_price['highest_buy_bid'];
    
  }
  return "Coins Not avalible";
 }
}
