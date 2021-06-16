<?php

namespace App\Http\Controllers;

use App\BitbnsApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\CryptoTrade;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $crypto_trad;
    public function __construct()
    {
      $this->crypto_trad=new CryptoTrade();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $usd_rate=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>'USD_INR'))->first();
        $in_inr=$usd_rate->setting_value;
        $date=Carbon::now()->subDays('1')->toDateString();
        $data_get=DB::table('order_table')->select("*")->where('created_at','>',$date)->where("order_status",">","1")->orderBy("id","desc")->paginate(10);
        $coins_value=DB::table("coin_blance")->select("*")->get();
        $symbol=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>'trade_coin'))->first();
        $coins_value_array=array();
        foreach ($coins_value as $c_data)
        {
            $coins_value_array[$c_data->coin_name]=$c_data->quantity;
        }
        $data['coins_value']=$coins_value_array;
        $data['open_oder']=$data_get;
        $previos_data=DB::table("price_data")->select("data_json")->orderBy("id","desc")->first();
        $data['symbol']=$symbol->setting_value;
        $data['inr_rate']=$in_inr;
        $data['coins_latest_price']=json_decode($previos_data->data_json,true);
        return view('dashboard',$data);
    }
    /**
     * @throws \Exception
     */
    public function binance_tiker_full_data()
    {  $usd_rate=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>'USD_INR'))->first();
        $symbol=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>'trade_coin'))->first();

        $binace = $this->crypto_trad->Bitbns_api->get_binance_api();
        $ticks = $binace->candlesticks($symbol->setting_value."USDT", "5m");
        $setting_data=array();
        foreach ($ticks as  $my_data)
        {
            $setting_data[]=array($my_data['openTime'],array(round($my_data['open']*$usd_rate->setting_value,2),round($my_data['high']*$usd_rate->setting_value,2),round($my_data['low']*$usd_rate->setting_value,2),round($my_data['close']*$usd_rate->setting_value,2)));
        }

        echo json_encode($setting_data);
    }
    /**
     *  function to save coin values
     * @param Request $req
     * @return string
     */
    function coin_info_set_data(Request $req)
    {
        $request_data=$req->all();
        $request_data['created_at']=Carbon::now()->toDateTimeString();
        $request_data['updated_at']=Carbon::now()->toDateTimeString();
        DB::table("coin_setting")->updateOrInsert(array("coin_name"=>$request_data['coin_name']),$request_data);
        return "Value updated successfully";

    }

    /**
     * @param Request $request
     * @return false|string
     */
    function create_sell_order_bitbns_form(Request $request)
    {
        $symbol=$request->input("symbol");
        $price_data=$this->crypto_trad->get_trade_price_by_coin($symbol);
        $buy_price=$price_data['highest_buy_bid'];
        if(isset($symbol)) {
            $data['quantity'] = $request->input("quantity");
            $data['rate'] = $buy_price;

           // $this->crypto_trad->check_bid_already_exist($symbol, $data['rate'], 1);
            $order_data = json_decode($this->crypto_trad->Bitbns_api->sell_order($symbol, $data), true);
            if ($order_data['status'] == "1") {
                $order_id = $order_data['id'];
                $quantity = $data['quantity'];
                $rate = $data['rate'];
                DB::table("order_table")->insert(array("order_id" => $order_id, "quantity" => $quantity, "price" => $rate, "coin" => $symbol, "order_status" => 0, "order_type" => 1, "created_at" => Carbon::now()->toDateTimeString(), "updated_at" => Carbon::now()->toDateTimeString()));

            } else {
                Log::error("Error in order Place:-" . json_encode($order_data));
            }
            return json_encode($order_data);
        }
        else {
            return  json_encode(array('data'=>'Please Select Coin','status'=>0));
        }
    }

    /**
     * @param Request $request
     * @return false|string
     */
    function create_buy_order_bitbns_form(Request $request)
    {
        $symbol=$request->input("symbol");
        $price_data=$this->crypto_trad->get_trade_price_by_coin($symbol);
        $sell_price=$price_data['lowest_sell_bid'];
        if(isset($symbol)) {
            $quantity_money=$request->input("quantity");
            $quantity=round($quantity_money/$sell_price,2);
            $data['quantity'] =$quantity;
            $data['rate'] = $sell_price;
            $order_data = json_decode($this->crypto_trad->Bitbns_api->create_order($symbol, $data), true);
            if ($order_data['status'] == 1) {
                $order_id = floatval($order_data['id']);
                $quantity = floatval($data['quantity']);
                $rate = $data['rate'];
                DB::table("order_table")->insert(array("order_id" => $order_id, "quantity" => $quantity, "price" => $rate, "coin" => $symbol, "order_status" => 0, "order_type" => 0, "created_at" => Carbon::now()->toDateTimeString(), "updated_at" => Carbon::now()->toDateTimeString()));
            } else {
                Log::error("Error in order Place:-" . json_encode($order_data));
            }
            return json_encode($order_data);
        }
        else {
            return  json_encode(array('data'=>'Please Select Coin','status'=>0));
        }
    }

    /**
     * @param Request $req
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     */
    function get_current_open_order(Request $req)
    {
        $body=$req->all();
        $get_order=$this->crypto_trad->Bitbns_api->listOpenOrders('DOGE',$body);
        return $get_order;

    }
    /**
     * @param Request $req
     * @return string
     */
    function update_setting_values(Request $req)
    {
        $settings=$req->all();
        if(is_array($settings['setting_value']))
        {
            $setting_value=json_encode($settings['setting_value']);
        }
        else{
            $setting_value=$settings['setting_value'];
        }
        DB::table("system_settings")->updateOrInsert(array('setting_key'=>$settings['setting_key']),array('setting_key'=>$settings['setting_key'],"setting_value"=>$setting_value,'created_at'=>Carbon::now()->toDateTimeString(),'updated_at'=>Carbon::now()->toDateTimeString()));
        return "setting has been added to system";
    }
//-----------------------main function to tracking on price data------------------------------------
    function main_thread_alog()
    {   //------------------get setting data---------------------------
        $usd_rate=$this->crypto_trad->get_setting_value('USD_INR');
        $stratgey=$this->crypto_trad->get_setting_value('strategy_value');
        $symbol =$this->crypto_trad->get_setting_value('trade_coin');
        //--------------------------get coin setting from coin-setting table---------------------------------
        $trade_setting=DB::table("coin_setting")->select("*")->where("coin_name",$symbol)->first();
        $max_qt=$trade_setting->slot_value;
        //-----------------------price is fetching from kucoin can chnage to binance---------------------
        $binace_price=$this->crypto_trad->get_coin_value_binance($symbol,$usd_rate);

        //---------% change of value from binance------------------------------
       // $percent_change_current_coin=self::get_price_change_in_five($symbol,$binace_price,$usd_rate);
        //-------------fetching data from kucoin-------------------------------------------
       // $percent_change_current_coin=$this->crypto_trad->get_price_change_in_database($symbol,$binace_price,$usd_rate);
        //--------------------end % changes in recent price-----------------------------
        $bitbns_tiker=$this->crypto_trad->get_trade_price();
        //------------   get the minutes-------------------------
        $upside_minutes=$this->crypto_trad->get_setting_value("up_side_minutes");
        //---------------------price data has been changes in database--------------------------
        $last_change=$this->crypto_trad->get_price_change_minutes_db($symbol,$upside_minutes);
        $last_price=$last_change[count($last_change)-1];
        $per_change=$this->crypto_trad->get_percentage_change($last_change[0],$last_price);
        $buy_diff=round($trade_setting->sell_deff);
        //----------------place order by price data compiare------------------------------
        $return_data_array=array("coin"=>$symbol,"mainPer"=>round($per_change,2),"coin_price"=>round($binace_price*$usd_rate,3),'bitbns_price'=>$bitbns_tiker['highest_buy_bid']);
        //-------strategy will define buy on dip or buy on bull it will change according trand----
        if($stratgey==1) {
            $this->crypto_trad->bullish_strategy($per_change, $trade_setting, $symbol, $bitbns_tiker, $binace_price);
        }
        else if($stratgey==0)
        {
            $this->crypto_trad->bear_strategy($per_change, $trade_setting, $symbol, $bitbns_tiker, $binace_price);
        }
        return $return_data_array;

    }
//------------------------seprate thread for bitcoin data------------------------
    function main_thread_binance_btc_two_minutes()
    {   $usd_rate=$this->crypto_trad->get_setting_value('USD_INR');
        $symbol=$this->crypto_trad->get_setting_value('Trade_Coin');
        //--------------------------get coin setting from coin-setting table---------------------------------
        $trade_setting=DB::table("coin_setting")->select("*")->where("coin_name",$symbol)->first();
        $max_qt=$trade_setting->slot_value;
        //--------------------end % changes in recent price-----------------------------
        $bitbns_tiker=$this->crypto_trad->get_trade_price();
        //-------------bitcoin value from binance---------------------------------------
        $bitcoin_price=round($this->crypto_trad->get_coin_value_binance("BTC",$usd_rate),5);
        $percent_change_bitcoin=$this->crypto_trad->get_price_change_in_15("BTC",$bitcoin_price,$usd_rate);
        //-------------------------------get setting value------------------------------------
        $btc_up=$this->crypto_trad->get_setting_value('BTC_UP');
        $btc_down=$this->crypto_trad->get_setting_value('BTC_DOWN');
        $return_data_array=array("coin"=>$symbol,"coin_price"=>$bitcoin_price,'bitbns_price'=>$bitbns_tiker['highest_buy_bid'],"Price_change"=>$percent_change_bitcoin);
        if($percent_change_bitcoin <= $btc_down)
        {   $this->crypto_trad->alert_telegram_chat("Bitcoin is GoingDown fast $percent_change_bitcoin % changed");
            $sell_price=$bitbns_tiker['highest_buy_bid'];
            $status_bid=$this->crypto_trad->check_bid_already_exist($symbol,$sell_price,1);

                $coin_blance=DB::table('coin_blance')->select("quantity")->where("coin_name",$symbol)->first();
                if($coin_blance > 0) {
                    $body['quantity'] = $coin_blance->quantity;
                    $body['rate'] = $sell_price;
                    $this->crypto_trad->create_sell_order_bitbns($symbol, $body);
                    Log::emergency("coin sell order has placed successfully");
                }
                else{
                    Log::emergency("All Coin Has been sold");
                }

        }
        //------------------buy on bitcoin base direct buy----------------------------
        else if($percent_change_bitcoin > $btc_up)
        {   $this->crypto_trad->alert_telegram_chat("Bitcoin is rising fast $percent_change_bitcoin % changed");
            $purchase_price=$bitbns_tiker['lowest_sell_bid'];
            $status_bid=$this->crypto_trad->check_bid_already_exist($symbol,$purchase_price,0);
                $body['quantity']=$max_qt;
                $body['rate']=$purchase_price;
                $inr_blance=DB::table('coin_blance')->select("quantity")->where("coin_name","Money")->first();
                if($inr_blance->quantity > $max_qt*$purchase_price ) {
                    $this->crypto_trad->create_buy_order_bitbns($symbol, $body);
                    Log::emergency("coin order has placed successfully");
                }
                else{
                    Log::emergency("Money has finished");
                }


        }

        //----------------------------end sale order block--------------------------------------

        return $return_data_array;

    }


//----------------job done in 1 minutes-----------------------------
    function run_in_every_minute()
    {
        $this->crypto_trad->insert_price_to_db_every_minute();
        $get_bot_status=$this->crypto_trad->get_setting_value("run_the_bot");
        if($get_bot_status==1) {
            self::main_thread_alog();
        }

    }


//----------function running on every 5 minutes new function--------------------------------------
    function run_on_every_five_minute()
    {
        $this->crypto_trad->sys_order_status();
        $this->crypto_trad->update_coin_balance();
        $this->crypto_trad->update_usd_to_db();

        return "five minute cron sucess";
    }
//-----------run in every 15 run-----------------
    function run_every_fifty_minutes()
    {
        $this->crypto_trad->sys_order_book_by_coin();
        $this->crypto_trad->clear_old_price_data_entries();
        $this->crypto_trad->check_all_commands();
    }
//-----------run in every 2 run-----------------
    function run_every_two_minutes()
    {

        $symbol=$this->crypto_trad->get_setting_value("trade_coin");
        $this->crypto_trad->cancel_all_bid_current_coin($symbol);
        $get_bot_status=$this->crypto_trad->get_setting_value("run_the_bot");
        if($get_bot_status==1) {
          self::main_thread_binance_btc_two_minutes();
        }
        $this->crypto_trad->binance_price_rising_alert();
    }


  function sys_all_data_in_one()
  {
      $this->crypto_trad->sys_order_book_by_coin();
      $this->crypto_trad->sys_order_status();
      $this->crypto_trad->update_coin_balance();
      $this->crypto_trad->update_usd_to_db();
      return "true";
  }

  function run_all_bot_alog()
  {
      self::main_thread_alog();
      self::main_thread_binance_btc_two_minutes();
      return "true";
  }

  function change_coin_price_command(Request $req)
  {   $symbol=$req->input('symbol');
      $this->crypto_trad->to_run_trade_coin_hook($symbol);
      return "success";
  }

function test_block_for_every_hook()
{
    $this->crypto_trad->check_all_commands();
    return "success";
}

}
