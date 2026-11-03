@extends('layouts.manifest')

@section('title')
    Delivery Note
@endsection

@section('content')

    @livewireStyles
    @livewire('trips.delivery-note-document', ['trip' => $trip, 'token' => $token ?? null])

@endsection

@section('extra-js')
    @include('trips.partials.signature-pad')
@endsection
