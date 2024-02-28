@extends('adminlte::page')
@section('title', '機材予約フォーム')
@section('css')
<link href="{{asset('/css/style.css')}}" rel="stylesheet" type="text/css">
@endsection
<script src="{{ asset('js/pctool_hide.js') }}"></script>

@section('content')
<h1 class="p-2">@yield('title')</h1>
  <div class="box1000 ">
    <p>
      使用期間を入力すると、期間内に使用可能な機材が一覧表示されます。<br>
      準備・配送に要する期間を確保するため、「機材納品日」は<b>翌営業日より数えて3営業日目（{{ App\Libs\Common::dayafter(today(),4)->isoFormat('YYYY年M月D日（ddd）'); }}～）以降
        </b>のみ選択可能です。<br>
    <b> ＜参考＞</b>荷物の配送所要日数は<a href="http://date.kuronekoyamato.co.jp/date/Main?LINK=TK" target="_blank"><b>こちら</b></a>から検索できます（ヤマト運輸のサイトが開きます）

    </p>
  </div>
  <form method="post" action="pctool">
    @csrf
<div class="container darkgray box1000">
    <div class="row">
      <div class="column col-8">
        <div class="row">
          <div class="col text-center p-1">
          <label>機材納品日</label>
          <input type="date" name="from" value="{{$input->from}}{{ old('from') }}" onchange="submit(this.form)">
          </div>
          <div class="col text-center p-1">
            <label>現場最終日</label>
            <input type="date" name="to" value="{{$input->to}}{{ old('to') }}" onchange="submit(this.form)">
          </div>
        </div>
      </div>
      <div class="column text-lest align-left p-1">
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" id="show_used" ><label class="custom-control-label" for="show_used">予約中の機材も表示する</label><br>
        </div>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="show_prepare" ><label class="custom-control-label" for="show_prepare">配送（返却）準備中の機材も表示する</label>
        </div>
      </div>
    </div>
</div>

</form>
  @if(count($errors)>0)
  <div class="container col-8">
      <ul class="text-red">
          @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
  @endif

{{-- <?php dump($records,$usage,$input->from, $input->session());?> --}}
  @if(!empty($inUse))
    {{implode(',', $inUse)}}
  @endif
<div id='list'>
  {{ Form::open(['route' => 'addCart', 'id' => 'pctool']) }}
    {{ Form::hidden('user_id', $user->id) }}
    {{ Form::hidden('from', $input->from)}}
    {{ Form::hidden('to', $input->to)}}
    <table class="table table-striped table-sm" id="pctool">
      <tr class="midashi">
        <th>　</th>
        <th>ID</th>
        <th>機材番号</th>
        {{-- <th>状態</th> --}}
        <th>型番</th>
        <th>導入年月</th>
        <th>OS</th>
        <th>CPU</th>
        <th>メモリ</th>
        <th>モニタ</th>
        <th>PPT</th>
        <th>カメラ</th>
        <th>BD/DVD</th>
        <th>Video</th>
        <th>toWin11</th>
        <th>備考</th>
      </tr>
      <?php dump($prepare, $usage);?>
      @foreach($records as $record)
        <tr class="@php 
        if(in_array($record->machine_id, $usage) && in_array($record->machine_id, $prepare)){
          echo('trused');
        }elseif(in_array($record->machine_id, $prepare) && !in_array($record->machine_id, $usage)){
          echo('trprepared');
        }elseif(!in_array($record->machine_id, $prepare) && in_array($record->machine_id, $usage)){
          echo('trused');
        }
        @endphp">
          <td class="text-center"><input type="checkbox" name="id[]" value="{{$record->machine_id}}"
            class="@php 
            if(in_array($record->machine_id, $usage) && in_array($record->machine_id, $prepare)){
              echo('chused');
            }elseif(in_array($record->machine_id, $prepare) && !in_array($record->machine_id, $usage)){
              echo('chprepared');
            }elseif(!in_array($record->machine_id, $prepare) && in_array($record->machine_id, $usage)){
              echo('chused');
            }
            @endphp"
            @if ($input->id <> null)
              {{ in_array($record->machine_id, $input->id)? ' checked' : '' }}
            @endif></td>
          <td>{{$record->machine_id}}</td>
          <td><a href="pctool/detail/{{$record->machine_id}}" target="_blank">{{$record->machine_name}}</a></td>
          {{-- <td class="p-1">{{$record->machine_status}}</td> --}}
          <td>{{$record->machine_spec}}</td>
          <td>{{Carbon\Carbon::parse($record->machine_since)->format('Y-m')}}</td>
          <td>{{$record->machine_os}}</td>
          <td>{{$record->machine_cpu}}</td>
          <td>{{$record->machine_memory}}</td>
          <td>{{$record->machine_monitor}}</td>
          <td>{{$record->machine_powerpoint}}</td>
          <td>{{$record->machine_camera == true ? '有' : '無'}}</td>
          <td>{{$record->machine_hasdrive == true ? '有' : '無'}}</td>
          <td>{{$record->machine_connector}}</td>
          <td>{{$record->machine_canto11}}</td>
          <td>{{$record->machine_memo}}</td>
        </tr>
      @endforeach
    </table>
</div>
  <p class="text-center p-2 m-0"><button type="submit" form="pctool" class="m-1">カートに入れる</button></p>
  {{ Form::Close() }}
</div>
@endsection

{{-- @section('footer')
(c)2023 Dai-oh Co., Ltd.
@endsection --}}
