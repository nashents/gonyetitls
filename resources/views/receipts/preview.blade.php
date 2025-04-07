
@extends('layouts.main')
@section('title')
Receipt Preview |@if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            @livewire('receipts.preview', ['receipt' => $receipt,
            'company' => $company,])
        </div>
    </div>
</div>
@endsection
    
