@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header">{{ $title ?? ui('page') }}</div>
<div class="card-body">{{ ui('coming_later') }}</div></div>
@endsection
