@extends('layout.auth')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100 form-reset">
        <div class="col-4"></div>        
        <div class="col-8">
            <div class="panel panel-default col-12 col-md-8 col-lg-6 mx-auto">

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal reset-form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}
                        <h3 class="title">Reset your password</h3>
                        <p>What your email, name or username? </p>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="col-md-3"></div>
                            <div class="col-md-11">
                                <input id="email" type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ old('email') }}" required placeHolder="Email">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <a href="{{ url('login') }}" class="btn btn-success">Back to Login</a>
                                &nbsp; 
                                <button type="submit" class="btn btn-primary">
                                    Send Password Reset Link
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
