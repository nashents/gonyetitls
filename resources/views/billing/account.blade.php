@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Billing &amp; Subscription | {{ $company->name }}
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                              @include('includes.top-message')
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                                        @if ($managing)
                                            <li><a href="{{ route('billing.index') }}"><i class="fa fa-list"></i> Client Billing</a></li>
                                            <li class="active">{{ $company->name }}</li>
                                        @else
                                            <li class="active"> <i class="fa fa-file-text-o"></i> Billing &amp; Subscription</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('billing.account', ['companyId' => $company->id])

                    </div>

@endsection

@section('extra-js')
    <script>
        window.addEventListener('billing-modal', function (event) {
            $('#' + event.detail.id).modal(event.detail.action);
        });
    </script>
@endsection
