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
    Debtors Journal | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                              @include('includes.top-message')
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('customer_statements.index')}}"><i class="fa fa-list"></i> Customer Statements</a></li>
            							<li class="active"> <i class="fa fa-book"></i> Debtors Journal</li>
            						</ul>
                                </div>
                            </div>
                        </div>

                        @livewire('debtor-journals.index')

                    </div>

@endsection

@section('extra-js')
    <script type="text/javascript">
        window.addEventListener('show-djCreateModal', event => { $('#djCreateModal').modal('show'); });
        window.addEventListener('hide-djCreateModal', event => { $('#djCreateModal').modal('hide'); });
        window.addEventListener('show-djAllocateModal', event => { $('#djAllocateModal').modal('show'); });
        window.addEventListener('hide-djAllocateModal', event => { $('#djAllocateModal').modal('hide'); });
        window.addEventListener('show-djVoidModal', event => { $('#djVoidModal').modal('show'); });
        window.addEventListener('hide-djVoidModal', event => { $('#djVoidModal').modal('hide'); });
    </script>
@endsection
