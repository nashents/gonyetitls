@extends('layouts.jobcard')

@section('title')
    GRNote   
@endsection

@section('content')
    
    @livewire('goods-receiveds.preview', ['goods_received' => $goods_received])

@endsection