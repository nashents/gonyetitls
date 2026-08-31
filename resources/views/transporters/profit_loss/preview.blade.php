
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
@php
    $selected_transporter = App\Models\Transporter::find($selectedTransporter);
@endphp
{{$selected_transporter?->name.'_Statement'
    .'_'.\Carbon\Carbon::parse($from)->format('Y-m-d')
    .'_'.\Carbon\Carbon::parse($to)->format('Y-m-d')
    .'_Profit_and_Loss'}}
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            @livewire('transporters.profit-loss.preview', [
                'selectedTransporter' => $selectedTransporter,
                'from' => $from,
                'to' => $to,
                ])
        </div>
    </div>
</div>


@endsection
