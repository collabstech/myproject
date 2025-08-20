@extends('layout')
@section('body')
    @yield('content')
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection