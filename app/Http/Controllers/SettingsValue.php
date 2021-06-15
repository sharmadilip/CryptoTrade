<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\CryptoTrade;
class SettingsValue extends Controller
{
    public function edit()
    { $data=array();
        $table_data=DB::table("system_settings")->get();
        $data['table_data']=$table_data;
        return view('pages.settings',$data);
    }

    /**
     * Update the profile
     *
     * @param  \App\Http\Requests\ProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $data=$request->all();

        unset($data['_token']);
        unset($data['_method']);
        foreach ($data as $key=>$value)
        {
            DB::table("system_settings")->updateOrInsert(array("setting_key"=>$key),array("setting_key"=>$key,"setting_value"=>$value,"created_at"=>Carbon::now()->toDateTimeString(),"updated_at"=>Carbon::now()->toDateTimeString()));
        }
        //------run node js again-----------------
        $trade=new CryptoTrade();
        $trade->to_run_kill_previos_node_hook();
        return back()->withStatus(__('Setting successfully Updated.'));
    }
    public function addSetting(Request $request)
    {   $data['setting_label']=$request->input("setting_label");
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
}
