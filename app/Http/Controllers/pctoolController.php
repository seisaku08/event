<?php

namespace App\Http\Controllers;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Http\Request;
use App\Libs\Common;
use App\Models\MachineDetail;
use App\Models\DayMachine;
use App\Models\Temporary;
use App\Models\User;
use App\Models\Order;
use App\Models\Maintenance;
use App\Models\Supply;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yasumi\Yasumi;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;

class pctoolController extends Controller
{
    public function view(Request $request){

        $data = [
            'records' => MachineDetail::all(),
            'user' => Auth::user(),
            'input' => $request,
        ];

        //使用日関連の変数を作る
        $day4after = Common::dayafter(today(),4);
        $day_after_from = Common::dayafter(Carbon::parse($request->from),1);
        // dd($request,$day1after,$daysemi3after);
        $validator = Validator::make($request->all(),
        [
            'from' => ['required_with_all:to', "after_or_equal:{$day4after}"],
            'to' => ['required_with_all:from', "after:from"],
        ],
        [
            'from.required_with_all' => '機材納品日は入力必須（'.$day4after->format('Y/m/d').'から入力可能）です。',
            'to.required_with_all' => '現場最終日は入力必須です。',
            'from.after_or_equal' => '機材納品日は'.$day4after->format('Y/m/d').'から入力可能です。',
            'to.after' => '現場最終日は機材納品日の翌日以降（'.$day_after_from->format('Y/m/d').'）から入力可能です。',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->except('to'));
        }

        //使用状況の確認（From:機材納品日からTo:現場最終日の間にday_machineテーブルに存在するmachine_idをピックアップする）
        if($request->from != "" && $request->to != ""){
            $arrive = new Carbon($request->from);
            $useend = new Carbon($request->to);
            $from = Common::daybefore($arrive, 3);
            $to = Common::dayafter($useend, 3);
            while($from <= $to){
                $u[] = $from->format('Y-m-d');
                $from->modify('1 day');
            }
            $dm = array_keys(array_count_values(DayMachine::whereIn('day', $u)->pluck('machine_id')->toarray()));
            $tm = array_keys(array_count_values(Temporary::whereIn('day', $u)->where('user_id', '<>', Auth::user())->pluck('machine_id')->toarray()));
            $data['usage'] = array_merge($dm, $tm);
        }else{
            $data['usage'] = [];
        }
        
        // dd($dm,$tm,$data['usage']);
        return view('pctool', $data);
    }
    public function retry(Request $request){
        $data = [
            'records' => MachineDetail::all(),
            'user' => Auth::user(),
            'input' => $request,
            
        ];
        if($request->session()->has('Session.CartData')){
            $merge['id'] = $request->session()->get('Session.CartData');
        }
        if($request->session()->has('Session.Arrive')){
            $merge['from'] = $request->session()->get('Session.Arrive');
        }
        if($request->session()->has('Session.UseEnd')){
            $merge['to'] = $request->session()->get('Session.UseEnd');
        }
        
        if(isset($merge)){
            $request->merge($merge);
        }

        //使用状況の確認（From:機材納品日からTo:現場最終日の間にday_machineテーブルに存在するmachine_idをピックアップする）
        if($request->from != "" && $request->to != ""){
            $arrive = new Carbon($request->from);
            $useend = new Carbon($request->to);
            $from = Common::daybefore($arrive, 3);
            $to = Common::dayafter($useend, 3);
            while($from <= $to){
                $u[] = $from->format('Y-m-d');
                $from->modify('1 day');
            }
            $dm = array_keys(array_count_values(DayMachine::whereIn('day', $u)->pluck('machine_id')->toarray()));
            $tm = array_keys(array_count_values(Temporary::whereIn('day', $u)->where('user_id', '<>', Auth::user()->id)->pluck('machine_id')->toarray()));
            $data['usage'] = array_merge($dm, $tm);
        }else{
            $data['usage'] = [];
        }
        
        // dd($dm,$tm,$data['usage']);
        return view('pctool', $data,);
    }
    //
    public function detail(Request $request){
        $id= $request->id;
        $data = [
            'id'=> $id,
            'machine_details' => MachineDetail::find($id),
            'supplies' => Supply::where('machine_id', '=', $id)->get(),
            'orders' => Order::join('machine_detail_order','orders.order_id','=','machine_detail_order.order_id')
                ->join('machine_details','machine_detail_order.machine_id','=','machine_details.machine_id')
                ->where('machine_details.machine_id',$id)
                ->orderBy('order_use_from', 'asc')
                ->get(),
            'maintenances' => Maintenance::where('machine_id',$id)->get()
        ];
        // dd($data);
        if($data['machine_details'] == null){
            return view('pctool/error', $data);
        }
        return view('pctool/detail', $data);
    }
}
