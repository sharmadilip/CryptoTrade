<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StrategyController extends Controller
{

    public function index()
    { $data=array();
        $table_data=DB::table("strategy_data")->paginate(10);
        $data['table_data']=$table_data;
        return view('pages.strategy_list',$data);
    }
    public function add(Request $request )
    {   if(isset($request->id)) {
        $id = $request->id;
        $data_get=DB::table("strategy_data")->select("*")->where("id",$id)->get()->first();
        $data['data']=$data_get;
        return view('pages.strategy_edit',$data);
    }
    else {

        return view('pages.strategy_add');
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
            DB::table("strategy_data")->updateOrInsert(array("id"=>$edit_id),$request_data);
            return back()->withStatus(__('Strategy Updated successfully added.'));
        }
        else {
            DB::table("strategy_data")->insert($request_data);
            return back()->withStatus(__('Strategy Added successfully added.'));
        }


    }
}
