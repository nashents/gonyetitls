
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Remittance Advice Preview |@if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="print-area">
            <div id="invoice">
                <x-loading/>
                <div class="toolbar hidden-print">
                    <div class="text-end">
                        <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left" style="color:black"></i> Back</button>
                        <a href="javascript:void(0)" onclick="printSection()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                        <a href="{{route('remittance_advice.pdf',['payment' => $payment->id])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF</a>
                        @if ($vendor->email)
                        <a href="{{route('remittance_advice.email',['payment' => $payment->id])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-envelope" style="color:red"></i> Send Email</a>
                        @endif
                    </div>
                    <hr>
                </div>
                <div class="invoice overflow-auto">
                    @include('includes.messages')
                    <div style="min-width: 600px">
                        @include('remittance_advice._advice')
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>


@endsection
