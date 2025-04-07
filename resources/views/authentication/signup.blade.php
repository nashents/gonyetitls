@extends('layouts.app')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/tinmac-favicon.png')!!}">
@section('title')
    Signup | Gonyet TLS
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
                                    <a href="{{route('login')}}"><img src="{{asset('images/gonyeti-mini.png')}}" alt="Gonyeti TLS" style="margin-bottom: -50px"></a>
                                    <h4>Gonyeti TLS Login</h4>
                                </div>
                                @include('includes.messages')
                            </div>

                                @livewire('authentication.signup')

                            <hr>

                            <p class="text-muted text-center mb-n"><small>Copyright © Gonyeti TLS {{date('Y')}}</small></p>
                        </div>
                        <!-- /.login-box -->
                    </div>
                    <!-- /.col-md-6 col-md-offset-3 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /. -->


@endsection
