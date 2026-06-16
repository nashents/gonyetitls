@extends('layouts.auth_app')
@section('title')
    Login | Gonyeti
@endsection
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/favicon.png')!!}">
@endsection
@section('body-id')
<body class="">
@endsection
@section('content')

            <div class="login-bg">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="login-box">
                            <div class="panel-heading">
                                <div class="panel-title text-center">
                                    <img src="{{asset('images/logo.png')}}" style="width:50%; height:50%" alt="Gonyeti TLS" >
                                    <h4 style="margin-top: -3%">Transport Management System</h4>
                                </div>
                                @include('includes.messages')
                            </div>

                            <form action="{{route('postLogin')}}" method="POST">
                                {{ csrf_field() }}
                            	<div class="form-group">
                            		<label for="exampleInputEmail1">Username</label>
                                    <input type="text" name="username" class="form-control input-lg" id="exampleInputEmail1" placeholder="Enter Your Username" autocomplete="off" required >
                            	</div>
                            	<div class="form-group">
                            		<label for="exampleInputPassword1">Password</label>
                            		<input type="password" name="password" class="form-control input-lg" id="exampleInputPassword1" placeholder="Password" required>
                            	</div>

                                <div class="form-group mt-20">
                                    <div class="">
                                        {{-- <a href="{{route('signup')}}" class="form-link"><small class="muted-text">Register?</small></a>
                                        <br> --}}
                                        <a href="{{ route('forgot-password') }}" class="form-link"><small class="muted-text">Forgot Password?</small></a>

                                        <button type="submit" class="btn btn-success btn-labeled pull-right">Sign in<span class="btn-label btn-label-right"><i class="fa fa-check"></i></span></button>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </form>

                            <hr>

                            <p class="text-muted text-center mb-n"><small>Copyright © Gonyeti ERP {{date('Y')}}</small></p>
                        </div>
                        <!-- /.login-box -->
                    </div>
                    <!-- /.col-md-6 col-md-offset-3 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /. -->


@endsection
