<?php

namespace App\Http\Controllers;

use App\Models\MachineDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConfirmController extends Controller
{
    //
    public function post(Request $request){

        $mid = $request->session()->get('Session.CartData');
        $data = [
            'machines' => MachineDetail::whereIn('machine_id', $mid)->get(),
            'user' => Auth::user(),
            'input' => $request

        ];

        $rules = [
                //
                'event_name' => 'required',
                'venue_zip' => 'exclude_if:event_venue_pending,true|required',
                'venue_addr1' => 'exclude_if:event_venue_pending,true|required',
                'venue_name' => 'exclude_if:event_venue_pending,true|required',
                'venue_tel' => 'exclude_if:event_venue_pending,true|required|digits_between:5,11',
                'shipping_arrive_day' => "exclude_if:event_venue_pending,true|required|after_or_equal:{$request->pend_arrive_day}",
                'shipping_return_day' => 'exclude_if:event_venue_pending,true|required|after_or_equal:order_use_to',
                'shipping_note' => 'max:200',
            ];

        $massages = [
                'event_name.required' => 'イベント名は必ず入力してください。',
                'venue_zip.required' => '郵便番号は必ず入力してください。',
                'venue_addr1.required' => '住所は必ず入力してください。',
                'venue_name.required' => '配送先担当者は必ず入力してください。',
                'venue_tel.required' => '配送先電話番号は必ず入力してください。',
                'venue_tel.digits_between' => '配送先電話番号は市外局番から入力してください。',
                'shipping_arrive_day.required' => '到着希望日は必ず入力してください。',
                'shipping_arrive_day.after_or_equal' => "到着希望日は事前に設定した機材納品日（{$request->pend_arrive_day}）以降の日付（当日含む）を入力してください。",
                'shipping_return_day.required' => '返送機材発送予定日は必ず入力してください。',
                'shipping_return_day.after_or_equal' => '返送機材発送予定日は現場最終日以降の日付（当日含む）を入力してください。',
                'shipping_note.max' => '備考は200文字以下で入力してください。',

               
            ];


        if($request->input('back') == 'back'){
            return redirect()->action('CartController@view');
        }
    
    
        $validator = Validator::make($request->all(), $rules, $massages);
        if($validator->fails()){
            return redirect()->route('sendto')->withErrors($validator)->withInput();
        }


        return view('confirm', $data);
    }
}
