@php
    // Sent via Mail::send() without a company in the data - use the sender's.
    $company = $company ?? optional(optional(auth()->user())->employee)->company ?? optional(auth()->user())->company;
@endphp
@extends('layouts.emails')
@section('title')
Fuel Request | {{ $company->name ?? '' }}
@endsection
@section('heading')
Fuel Request
@endsection
@section('content')
<p style="margin:0 0 14px 0;">{{ $supplier }},</p>
<p style="margin:0 0 14px 0;">Please provide <strong>{{ $driver }}</strong> with <strong>{{ $quantity }} litres</strong> of <strong>{{ $fuel_type }}</strong>.</p>
@include('emails.partials.details', ['rows' => [
    'Driver' => $driver,
    'Vehicle' => $regnumber ?? null,
    'Fuel type' => $fuel_type,
    'Quantity' => $quantity . ' litres',
    'Authorized by' => $authorized_by,
    'Authorized on' => $date,
]])
<p style="margin:0;color:#6b7480;font-size:13px;">{{ $subject }}</p>
@endsection
