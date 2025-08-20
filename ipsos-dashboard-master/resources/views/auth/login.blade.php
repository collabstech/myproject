@extends('layout.auth')
@section('content')
<div class="container-fluid h-100">
    <div class="row h-100 login-page">
        <div class="col-4"></div>
        <div class="col-8">
            <form class="col-12 col-md-8 col-lg-6 mx-auto login-form" name="loginForm" action="{{ url('login') }}" method="POST">
                {{ csrf_field() }}
                <div class="form-group m-b-15 text-center">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="logo">
                    <br>
                    <span class="login-logo">IPSOS Online Dashboard</span>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-group m-b-15">
                    <input type="email" class="form-control input-lg" name="email" required placeholder="Email" value="{{ old('email') }}" />
                </div>
                <div class="form-group m-b-15">
                    <input type="password" class="form-control input-lg" name="password" required placeholder="Password" />
                </div>
                <div class="login-buttons">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
                </div>
                <div class="py-3"></div>
                <div class="text-center">
                    <a href="{{ url('password/reset') }}">Forgot Password</a>
                </div>
            </form>
            <div class="game-changer">
                <span>GAME CHANGERS</span>
            </div>
            <div class="text-center login-footer">
                Copyright &copy; IPSOS Online Dashboard 2018 version 1.1.0
            </div>
        </div>
    </div>
</div>
@endsection