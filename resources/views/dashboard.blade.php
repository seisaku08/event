@extends('adminlte::page')
@section('title', 'マイページ')
@section('css')
<link href="{{asset('/css/style.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')
<h1 class="text-center p-2">@yield('title')</h1>
        {{-- <?php dump($orders);?> --}}
    <div class="box1000">
        <p>こちらはマイページです。
        @can('sys-ad'){{-- 管理者に表示される --}}
        そしてあなたはシステム管理者です。
        @elsecan('daioh') {{-- 大應ユーザーに表示される --}}
        そしてあなたは大應の人です。
        @endcan
        </p>
        <p>ユーザー個人の登録イベント一覧や、各種情報（の概要）を要約したページにする予定です。</p>
        {{-- ユーザー認証 --}}
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="text-red text-bold">
                <p class="text-sm mt-2 text-gray-800">
                    メールアドレス認証が未完了です。<br>
                    未認証のメールアドレスによるアクセスでは、システム利用に制限がかかります。
                </p>

                    <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('こちらをクリックしてEメール認証を完了してください。') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('認証用のメールをご記入のアドレスに送信しました。') }}
                    </p>
                @endif
            </div>
        @endif
        <h5>最新の更新情報</h5>
        <ul>
            <li>空き状況検索画面において、検索条件を変更しました。<br>機材納品日と現場最終日を入力すると、その前後3営業日が自動的に事前メンテナンス・配送および返送準備日として確保されます。<br>この準備日は、大應に返送せず、前後利用者様との直接受け渡しをすることを条件に、予約が可能な期間となります（検索画面では水色のセルで表示されます）。（2024/2/27）</li>
        </ul>
            <p><a class="" data-toggle="collapse" href="#updateinfo" role="button" aria-expanded="false" aria-controls="updateinfo">過去の更新情報（クリックで開く）</a></p>
        <div class="collapse" id="updateinfo">
            <div class="card card-body">
                <ul>
                    <li>展示会用機材予約・管理サイトを作成しました。（2024/1/30）</li>
                </ul>
    </div>
</div>

        <h5>今後の予定</h5>
        <ul>
        </ul>
        </p>
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>
    <h4 class="text-bold">登録済みイベント</h4>
    <table id="kizai2" class="table table-striped table-sm caption-top">
        <thead class="thead-light">
            <tr>
                <th scope="col">期間</td>
                <th scope="col">予約No. </td>
                <th scope="col">イベント名</td>
                <th scope="col">現在の状態</td>
            </tr>
        </thead>
        @if(isset($orders))
        {{-- <?php dump($orders);?> --}}
            @foreach($orders as $order)
                <tr>
                    <td class="kizai-left">{{$order->order_use_from}}～{{$order->order_use_to}}</td>
                    <td class="kizai-right"><a href="order/detail/{{$order->order_id}}">{{$order->order_no}}</a></td>
                    <td class="kizai-right">{{$order->event_name}}</td>
                    <td class="kizai-right">{{$order->order_status}}</td>
                </tr>
            @endforeach
        @else
            <td class="kizai-left">データはありません。</td>
            <td class="kizai-right"></td>
            <td class="kizai-right"></td>
        @endif

    </table>
    　
@endsection