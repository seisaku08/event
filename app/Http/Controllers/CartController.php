<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MachineDetail;
use App\Models\DayMachine;
use App\Models\Temporary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use App\Libs\Common;



class CartController extends Controller
{

    public function index(Request $request){
        if(session()->has('Session.CartData') == false){
            $data = [
                'user' => Auth::user()->id,
                'input' => $request,
                'arrive' => $request->session()->get('Session.Arrive'),
                'useend' => $request->session()->get('Session.UseEnd'),
                'from' => $request->session()->get('Session.UseFrom'),
                'to' => $request->session()->get('Session.UseTo'),
                
            ];
            return view('cart.empty', $data);
        }

        $mid = $request->session()->get('Session.CartData');
        $data = [
            'user' => Auth::user()->id,
            'input' => $request,
            'CartData' => MachineDetail::wherein('machine_id', $mid)->get(),
            'arrive' => $request->session()->get('Session.Arrive'),
            'useend' => $request->session()->get('Session.UseEnd'),
            'from' => $request->session()->get('Session.UseFrom'),
            'to' => $request->session()->get('Session.UseTo'),
        
        ];
        return view('cart', $data);
    }

    public function addCart(Request $request,)
    {
        //使用機材のIDを取得
        $id = $request->input('id');

        $day4after = Common::dayafter(today(),4);
        $day_after_from = Common::dayafter(Carbon::parse($request->from),1);

        $validator = Validator::make($request->all(),
        [
            'from' => ['required_with_all:to', "after_or_equal:{$day4after}"],
            'to' => ['required_with_all:from', "after_or_equal:{$day_after_from}"],
            'id' => 'required',
        ],
        [
            'from.required_with_all' => '機材納品日は入力必須（3営業日後（'.$day4after->format('Y/m/d').'）から入力可能）です。',
            'to.required_with_all' => '現場最終日は入力必須です。',
            'from.after_or_equal' => '機材納品日は3営業日後（'.$day4after->format('Y/m/d').'）から入力可能です。',
            'to.after' => '現場最終日は機材納品日の翌日以降（'.$day_after_from->format('Y/m/d').'）から入力可能です。',
            'id' => '機材は必ず一つ以上選択してください。',
        ]);

        if($validator->fails()){
            //セッションに機材ID、日程を登録
            $request->session()->put('Session.CartData', $id);
            $request->session()->put('Session.Arrive', $request->from);
            $request->session()->put('Session.UseEnd', $request->to);
            $request->session()->put('Session.UseFrom', Common::daybefore(Carbon::parse($request->from), 3)->format('Y-m-d'));
            $request->session()->put('Session.UseTo', Common::dayafter(Carbon::parse($request->to), 3)->format('Y-m-d'));

            return redirect()->route('pctool.retry')->withErrors($validator);
        }

        //使用状況を確認
        //$uに検索日程を1日ずつ格納
        $arrive = new Carbon($request->from);
        $useend = new Carbon($request->to);
        $from = Common::daybefore($arrive, 3);
        $to = Common::dayafter($useend, 3);
        while($from <= $to){
            $u[] = $from->format('Y-m-d');
            $from->modify('1 day');
        }
        // dd($arrive, $useend, $from, $to, $usageday, $u);
        $user = Auth::user()->id;
        //検索日程における既登録分を取得
        $usage = DayMachine::whereIn('day', $u)->pluck('machine_id')->toarray();
        //temporariesテーブルから自分「以外」の仮登録状況を取得する
        $tempUse = Temporary::where('user_id', '<>', $user)->whereIn('day', $u)->pluck('machine_id')->toarray();
        //無限増殖防止のためtemporariesテーブルからユーザー自身の仮登録分を削除する
        Temporary::where('user_id',$user)->delete();

        $inUse = array_keys(array_count_values(array_merge($usage,$tempUse)));

        if(in_array($id, $inUse)){
            // return back()->withInput();
        }
        else{
            //temporariesテーブルに選択した機材ID、日程を仮登録
            foreach($id as $i){
                $arrive = new Carbon($request->from);
                $from = Common::daybefore($arrive, 3);
                while($from <= $to){
                    $temp = new Temporary;
                        $temp->user_id = $user;
                        $temp->machine_id = $i;
                        $temp->day = date($from->format('Y-m-d'));
                        $temp->save();
                    $from->modify('1 day');
                }
            }
            //セッションに機材ID、日程を登録
            $request->session()->put('Session.CartData', $id);
            $request->session()->put('Session.Arrive', $request->from);
            $request->session()->put('Session.UseEnd', $request->to);
            $request->session()->put('Session.UseFrom', Common::daybefore(Carbon::parse($request->from), 3)->format('Y-m-d'));
            $request->session()->put('Session.UseTo', Common::dayafter(Carbon::parse($request->to), 3)->format('Y-m-d'));

        }
        // dd($id,$u,$usage,$tempUse,array_merge($usage,$tempUse),$inUse,in_array($id, $inUse),$request->session());
        
        return redirect()->route('cart.index');
    }

    public function delCart(Request $request)
    {
        //セッションからカートデータを取り出し、消去
        $sessionCartData = $request->session()->get('Session.CartData');
        $request->session()->forget('Session.Cartdata');

        //削除対象を取り出したカートデータから削除する
        $removed = array_diff($sessionCartData, [$request->machine_id]);

        //temporariesテーブルから削除対象の仮登録分を削除する
        Temporary::where('machine_id',$request->machine_id)->delete(); 

        //削除済みの新カートデータをセッションに保存
        if(!empty($removed)){
            $request->session()->put('Session.CartData', $removed);
        }else{
            session()->forget('Session.CartData');
            return view('cart.empty');
        }
        
        // dd($request, $sessionCartData, $removed, $request->session()->get('Session.CartData'));
        return redirect()->route('cart.index');
    }

}
