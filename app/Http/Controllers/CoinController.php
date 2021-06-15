<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\CryptoTrade;

class CoinController extends Controller
{
    public function index()
    { $data=array();
        $table_data=DB::table("coin_setting")->paginate(10);
        $data['table_data']=$table_data;
        return view('pages.coin_list',$data);
    }
    public function coin_order_book()
    {
        $table_data=DB::table("order_table")->orderBy("id","desc")->paginate(10);
        $data['table_data']=$table_data;
        return view('pages.order_list',$data);
    }
    public function add(Request $request )
    {   if(isset($request->id)) {
        $id = $request->id;
        $data_get=DB::table("coin_setting")->select("*")->where("id",$id)->get()->first();
        $data['data']=$data_get;
        return view('pages.coins_edit',$data);
    }
    else {

        return view('pages.coins_add');
    }
    }
    public function save(Request $request)
    {
       $request_data=$request->all();
       $request_data['created_at']=Carbon::now()->toDateTimeString();
        $request_data['updated_at']=Carbon::now()->toDateTimeString();
        unset($request_data['_token']);
        unset($request_data['_method']);
        if(isset($request_data['edit_id']))
        {   $edit_id=$request_data['edit_id'];
            unset($request_data['edit_id']);
            DB::table("coin_setting")->updateOrInsert(array("id"=>$edit_id),$request_data);
            return back()->withStatus(__('Coin Updated successfully added.'));
        }
        else {
            DB::table("coin_setting")->insert($request_data);
            return back()->withStatus(__('Coin Added successfully added.'));
        }


    }
    public function update(Request $request)
    {
        $data['setting_key']=$request->input("setting_key");
        $data['setting_value']=$request->input("setting_value");
        $data['created_at']=Carbon::now()->toDateTimeString();
        if($data['setting_key']!=""&&$data['setting_value']!='') {
            DB::table("system_settings")->insert($data);

            return back()->withStatus(__('Setting successfully added.'));
        }
        else{
            return back()->withStatus(__('Setting add failed.'));
        }
    }

    public function buy_or_sell_data()
    {
        $trade_setting_row=DB::table("system_settings")->select('setting_value')->where(array('setting_key'=>'coins'))->first();
       $data['trade_coins']=json_decode($trade_setting_row->setting_value,true);
        return view("pages.buy_or_sell",$data);
    }

    public function buy_form_value_on_chnage(Request $request)
    {   $symbol=$request->input("symbol");
        $crpto_trade=new CryptoTrade();
        $crpto_trade->update_coin_balance();
        $coin_trade_data=DB::table("coin_setting")->select('*')->where(array('coin_name'=>$symbol))->first();
        $Inr_data=DB::table("coin_blance")->select('quantity')->where(array('coin_name'=>"Money"))->first();
        $coin_data=DB::table("coin_blance")->select('quantity')->where(array('coin_name'=>$symbol))->first();
        $data['coin_trade']=$coin_trade_data;
        $data['money_value']=$Inr_data->quantity;
        if(isset($coin_data->quantity)) {
            $data['coin_blance'] = $coin_data->quantity;
        }
        else{
            $data['coin_blance']=0;
        }
        return json_encode($data);

    }
    public function get_buy_or_sell(Request $request)
{   $symbol=$request->input("symbol");

    $crypto_trade=new CryptoTrade();
    $bitbns_data=json_decode($crypto_trade->Bitbns_api->get_ticker_by_coin(),true);
   $tiker_data= $bitbns_data[$symbol];

   return json_encode($tiker_data);
}
    public function clear_all_current_bids(Request $request)
    { $crypto_trade=new CryptoTrade();
        $symbol=$request->input("symbol");
        if(isset($symbol)) {
            $crypto_trade->cancel_all_bid($symbol);
            $crypto_trade->update_coin_balance();
            return "All bid has canceled";
        }
        else{
            return "please select Coin";
        }
    }

}
