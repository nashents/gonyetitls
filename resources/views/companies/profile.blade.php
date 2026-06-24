@extends('layouts.app')
@section('content')
@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (isset(Auth::user()->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Profile | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (isset(Auth::user()->company))
    {{Auth::user()->company->name}}
    @endif
@endsection
     

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                <h4 class="title"> {{$company->name}} <small class="ml-10">Profile</small></h4>

                                </div>
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i>Dashboard</a></li>
            							<li class="active"> <i class="fa fa-user"></i> Company Profile</li>
                                        <li class="active"> 
                                            {{$company->name}}
                                        </li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->
                                <div class="col-md-6 text-right">
                                    {{-- <a href="#"><i class="fa fa-comments"></i> Talk to us</a>
                                    <a href="#" class="pl-20"><i class="fa fa-cog"></i> Settings</a> --}}
                                </div>
                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->

                            <div class="row mt-30">
                                <div class="col-md-2">
                                    <div class="panel border-primary no-border border-3-top">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Company Logo</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                @livewire('companies.logo', ['company' => $company])
                                            </div>

                                        </div>
                                    </div>

                                </div>
                                <!-- /.col-md-3 -->

                                <div class="col-md-10">
                                    @include('includes.messages')
                                    <ul class="nav nav-tabs nav-justified" role="tablist">
                                		<li role="presentation" class="active"><a href="#personal" aria-controls="personal" role="tab" data-toggle="tab">
                                           {{$company->name}} Details</a></li>
                                        <li role="presentation"><a href="#hr" aria-controls="hr" role="tab" data-toggle="tab">HR</a></li>
                                        <li role="presentation"><a href="#invoices" aria-controls="invoices" role="tab" data-toggle="tab">Invoices & Quotations</a></li>
                                        <li role="presentation"><a href="#dates" aria-controls="dates" role="tab" data-toggle="tab">Dates & Currencies</a></li>
                                        <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                                        <li role="presentation"><a href="#bank_accounts" aria-controls="bank_accounts" role="tab" data-toggle="tab">Bank Accounts</a></li>
                                        <li role="presentation"><a href="#notifications" aria-controls="notifications" role="tab" data-toggle="tab">Notifications</a></li>
                                        <li role="presentation"><a href="#modules" aria-controls="modules" role="tab" data-toggle="tab">Modules</a></li>
                                        <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
                                        <li role="presentation"><a href="#budgets" aria-controls="budgtes" role="tab" data-toggle="tab">Budgets</a></li>
                                        <li role="presentation"><a href="#integrations" aria-controls="integrations" role="tab" data-toggle="tab">Integrations</a></li>
                                    </ul>
                                    @livewire('companies.profile', ['company' => $company])
                                   
                                </div>
                                <!-- /.col-md-9 -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->


                    </div>
                    <!-- /.main-page -->



        </div>
@endsection
@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#bank_accountsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#documentsTable').DataTable();
    } );
    </script>
@endsection
