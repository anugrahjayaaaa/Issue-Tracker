@extends('layouts.app')
@section('content')
<h3>{{ ui('new_user') }}</h3>
@include('access.users.edit')
@endsection
