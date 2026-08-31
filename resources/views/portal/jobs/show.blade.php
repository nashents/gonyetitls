@extends('layouts.customer_portal')

@section('title', 'Freight Job')

@section('content')
    @livewire('portal.job-show', ['jobId' => $jobId])
@endsection
