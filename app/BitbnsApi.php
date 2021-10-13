<?php


namespace App;
use Carbon\Carbon;
use Binance;

use Illuminate\Support\Facades\Http;


class BitbnsApi
{
    public   $api_base_ulr;
    public $api_base_version_new;
    //-----------bitbns-----------------------------
    public  $public_key;
    public  $secrete_key;
    //----------binance-----------------------------
    public  $binance_pulic_key;
    public  $binance_secrate_key;
    //--------------end keys--------------------
    public function __construct()
    {
        $this->public_key=env('BITBNS_KEY_PUBLIC',"83FCCF44961BE8FDF4AB2E659EDD035D");
        $this->secrete_key=env('BITBNS_KEY_SECRET',"6FA4608A229A458C789CE808A925A956");
        $this->binance_pulic_key=env('BINANCE_KEY_PUBLIC');
        $this->binance_secrate_key=env('BINANCE_KEY_SECRET');
        $this->api_base_ulr="https://api.bitbns.com/api/trade/v1/";
        $this->api_base_version_new="https://api.bitbns.com/api/trade/v2/";
    }
    //-----------------get all coins value-------------------------
    public function get_binance_api()
    {
        $api = new Binance\API( $this->binance_pulic_key,$this->binance_secrate_key );
        $api->caOverride = true;
        return $api;
    }
    public function get_binance_prices_usdt()
    {
        $api=self::get_binance_api();
        $price = $api->prices();

        $binance_price=array_filter($price, function($k) {
            return strpos($k,"USDT") != 0;
        }, ARRAY_FILTER_USE_KEY);
        return $binance_price;
    }
    public function get_kucoin_price_usdt()
    {
        $get_data=json_decode(Http::get("https://api.kucoin.com/api/v1/prices"),true);
        return $get_data['data'];
    }
    public function get_kucoin_price_symbol_usdt($symbol)
    {
        $get_data=json_decode(Http::get("https://api.kucoin.com/api/v1/prices"),true);
        return $get_data['data'][$symbol];
    }
    public function get_ticker()
    {
        $response=  Http::get("https://bitbns.com/order/fetchTickers");

        return json_decode($response->body(),true);
    }
    public function get_ticker_by_coin()
    {
        $response=  self::execute_single_call('tickers','');
        return $response;
    }
    //-----------------get buy orderbook-------------------------
    public function get_buy_order_book($symbol)
    {
        $response=  self::execute_single_call('orderbook/buy/'.$symbol,'');
        return $response;
    }
   public function get_websocket_token()
   {
       $response =self::execute_script_http('getOrderSocketToken/USAGE',array());
       return $response;
   }
    //-----------------get sell orderbook-------------------------
    public function get_sell_order_book($symbol)
    {
        $response=  self::execute_single_call('orderbook/sell/'.$symbol,'');
        return $response;
    }
function get_plateform_status()
{
    $response=  self::execute_single_call('platform/status','');
    return $response;

}
    //-------------------Get All Coin Balance--------------------------
    public  function get_api_status($symbol="USAGE",$post_data)
    {
        $response =self::execute_script_http('getApiUsageStatus/'.$symbol,$post_data);
        return $response;
    }
    //-------------------Get All Coin Balance--------------------------
    public  function AllCoinBalance($symbol="EVERYTHING",$post_data)
    {
        $response =self::execute_script_http('currentCoinBalance/'.$symbol,$post_data);
        return $response;
    }
//-------------------Get single Coin Balance--------------------------
    public  function currentCoinBalance($symbol,$post_data)
    {
        $response =self::execute_script_http('currentCoinBalance/'.$symbol,$post_data);
        return $response;
    }
    //---------------open order data----------------------
    public  function listOpenOrders($symbol,$post_data)
    {
        $response =self::execute_script_http('listOpenOrders/'.$symbol,$post_data);
        return $response;
    }

//---------------header create order data----------------------
    public  function create_order($symbol,$post_data)
{
    $response =self::execute_script_http('placeBuyOrder/'.$symbol,$post_data);
    return $response;
}

//---------------Order Status----------------------
    public  function orderStatus($symbol,$post_data)
    {
        $response =self::execute_script_http('orderStatus/'.$symbol,$post_data);
        return $response;
    }

//-----------------selling a order-----------------------------------------
    public  function sell_order($symbol,$post_data)
    {
        $response =self::execute_script_http('placeSellOrder/'.$symbol,$post_data);
        return $response;
    }

//-----------------buy stop loss-----------------------------------------
    public  function but_stop_loss($symbol,$post_data)
    {
        $response =self::execute_script_http('buyStopLoss/'.$symbol,$post_data);
        return $response;
    }

    //-----------------buy stop loss-----------------------------------------
    public  function sell_stop_loss($symbol,$post_data)
    {
        $response =self::execute_script_http('placeSellOrder/'.$symbol,$post_data);
        return $response;
    }
//-----------------Cancel Order-----------------------------------------
    public  function CancleOrder($symbol,$post_data)
    {
        $response =self::execute_script_http('cancelOrder/'.$symbol,$post_data);
        return $response;
    }

    //-----------------Cancel cancelStopLossOrder-----------------------------------------
    public  function cancelStopLossOrder($symbol,$post_data)
    {

        $response =self::execute_script_http('cancelStopLossOrder/'.$symbol,$post_data);
        return $response;
    }

    //-----------------Order list by date (2020-05-01T00:00:00Z)-----------------------------------------
    public  function order_list_data($symbol,$post_data)
    {
        $response =self::execute_script_http('listExecutedOrders/'.$symbol,$post_data);
        return $response;
    }
//---------------header signature data-----------------
    public function header_data($bodystring)
{

    $timeStamp_nonce =round(Carbon::now()->timestamp* 1000.0);
    $raw_payload = json_encode($bodystring);
    $secret = $this->secrete_key;
    $obj = array('timeStamp_nonce' => $timeStamp_nonce, 'body' => $raw_payload);
    $myJSON = json_encode($obj);
    $payload = base64_encode($myJSON);
    $sig = hash_hmac('sha512', $payload, $secret);

    return array('X-BITBNS-APIKEY'=>$this->public_key,'X-BITBNS-PAYLOAD'=>$payload,'X-BITBNS-SIGNATURE'=>$sig,'Accept' => 'application/json',
  'Accept-Charset' => 'utf-8','content-type' => 'application/x-www-form-urlencoded');
}

    public function execute_single_call($url_slug,$body=null)
    {   $post_url=$this->api_base_ulr;
        $response = Http::withHeaders(['X-BITBNS-APIKEY' => $this->public_key,'Cache-Control' => 'no-cache'])->get($post_url.$url_slug);
        return $response->body();
    }
//------------------exicute with hash code-----------------------------
public function execute_script_http($url_slug,$body)
{   $post_url=$this->api_base_ulr;
    $data='';
    if(!empty($body)) {
        $response = Http::withHeaders(self::header_data($body))->post($post_url . $url_slug, $body);
        $data=$response->body();
    }
    else{
        $data = $this->curl_for_empthy($url_slug);
    }
    return $data;
}
    public function execute_script_http_2($url_slug,$body)
    {   $post_url=$this->api_base_version_new;
        $response = Http::withHeaders(self::header_data($body))->post($post_url.$url_slug,$body);
        return $response;
    }
    function orders_execution_version_new($body)
    {
        $response =self::execute_script_http_2('orders/',$body);
        return $response;
    }
//--------------update $ price----------------------------------------------------------------
    public function update_usd_price()
    {
        $get_amount = Http::get("https://api.exchangerate-api.com/v4/latest/USD");
        $price=$get_amount['rates']['INR'];
        return $price;
    }

    public function curl_for_empthy($slug)
    {
        $base_url=$this->api_base_ulr;
        $t=round(microtime(true) * 1000);
        $timeStamp_nonce = strval($t);
        $secret = $this->secrete_key;
        $obj=array('timeStamp_nonce'=>$timeStamp_nonce,'body'=>'{}');
        $myJSON = json_encode($obj);
        $payload = base64_encode($myJSON);
        $sig = hash_hmac('sha512', $payload, $secret);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $base_url.$slug,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{}',
            CURLOPT_HTTPHEADER => array(
                'X-BITBNS-APIKEY:'.$this->public_key,
                'X-BITBNS-PAYLOAD:'.$payload,
                'X-BITBNS-SIGNATURE:'.$sig,
                'Accept: application/json',
                'Accept-Charset: utf-8',
                'content-type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;

    }
    public function header_data_empthy($symbol)
    {

        $timeStamp_nonce =round(Carbon::now()->timestamp* 1000.0);
        $secret = $this->secrete_key;
        $obj = array('symbol'=>'/'.$symbol,'timeStamp_nonce' =>$timeStamp_nonce, 'body' =>'{}');
        $myJSON = json_encode($obj);
        $payload = base64_encode($myJSON);
        $sig = hash_hmac('sha512', $payload, $secret);
        return array('X-BITBNS-APIKEY'=>$this->public_key,'X-BITBNS-PAYLOAD'=>$payload,'X-BITBNS-SIGNATURE'=>$sig,'Accept' => 'application/json','Accept-Charset' => 'utf-8','content-type' => 'application/x-www-form-urlencoded');
    }
}
