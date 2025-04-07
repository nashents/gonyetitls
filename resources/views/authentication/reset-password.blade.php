@extends('layouts.app')
@section('title')
    Reset Password | Gonyeti
@endsection
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/favicon.png')!!}">
@endsection
@section('body-id')
<body class="">
@endsection
@section('content')

            <div class="">
                <div class="row">
                    <div class="col-lg-6">
                        <section class="section">
                            <div class="row mt-40">
                                <div class="col-md-10 col-md-offset-1 pt-50">

                                @livewire('authentication.reset-password',['token'=>$token, 'user'=> $user])
                                    <!-- /.row -->
                                </div>
                                <!-- /.col-md-12 -->
                            </div>
                            <!-- /.row -->
                        </section>

                    </div>
                    <!-- /.col-lg-6 -->

                    <div class="col-lg-6 visible-lg-block">
                        <img src="{{ asset('images/photo-1.jpg') }}" alt="Options - Admin Template" class="img-responsive">
                    </div>
                    <!-- /.col-lg-6 -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /. -->
@endsection