@extends('layout')
@section('body')
<!-- begin #page-container -->
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
    <!-- begin #header -->
    @include('layout.header')
    <!-- end #header -->

    <!-- begin #sidebar -->
    @include('layout.sidebar')
    <div class="sidebar-bg"></div>
    <!-- end #sidebar -->

    <!-- begin #content -->
    <div id="content" class="content">
        <!-- begin breadcrumb -->
        <ol class="breadcrumb pull-right">
            @yield('breadcrumb')
        </ol>
        <!-- end breadcrumb -->
        <!-- begin page-header -->
        <h1 class="page-header">@yield('page-title') <small>@yield('page-subtitle')</small></h1>
        <!-- end page-header -->
        <hr>
        @yield('content')
    </div>
    <!-- end #content -->

    <!-- begin scroll to top btn -->
    <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top">
    <i class="fa fa-angle-up"></i>
    </a>
    <!-- end scroll to top btn -->
</div>
<!-- end page container -->
<!-- begin #footer -->
@include('layout.footer')            
    <!-- end #footer -->
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/master.css') }}">
@endsection