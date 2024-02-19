@extends('adminlte::page')
@section('title', "{$id} | 機材が見つかりません")
@section('css')
<link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">

@endsection
@section('content')
<h1 class="p-3">@yield('title')</h1>

<article id="detail">
<p>機材が見つかりませんでした。初めからやり直してください。</p>
<p class="text-center mt-3" ><button onclick="window.close();">画面を閉じる</button></p>
</article>
@endsection
