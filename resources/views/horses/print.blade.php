
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Horses Report Preview |@if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice">
                <x-loading/>
          
                <div class="invoice overflow-auto">
                    <div style="margin-left: -25px;">
                        <header>
                            <div class="row">
                                <div class="col">
                                    <a href="javascript:;">
                                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                                </a>
                                               
                                </div>
                                <div class="col company-details">
                                    <h4 class="name" >
                                        <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                    {{$company->name}}
                                    </a>
                                    </h4>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                    <div>{{$company->phonenumber}}
                                    </div>
                                    <div>{{$company->email}}</div>
                                </div>
                            </div>
                        </header>
                        @if ($selectedFilter == "revenue")
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to" >
                                 
                                </div>
                                <div class="col invoice-details">
                                    <h4 class="to" style="margin-bottom: -1px;">Revenue Generated Report</h4> 
                                    @if (isset($from) && isset($to))
                                    <div class="date" style="padding-bottom: 3px"> <strong>From: </strong>{{ date('F j, Y', strtotime($from)) }}</div>
                                    <div class="date" style="padding-bottom: 3px"> <strong>To: </strong>{{ date('F j, Y', strtotime($to)) }}</div>
                                    @endif
                                </div>
                            </div> 
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"><strong>Transporter</strong></th>
                                        <th class="text-center"><strong>Horse#</strong></th>
                                        <th class="text-center"><strong>Make</strong></th>
                                        <th class="text-center"><strong>Model</strong></th>
                                        <th class="text-center"><strong>HRN</strong></th>
                                        <th class="text-center"><strong>Revenue</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($horses as $horse)
                                        @php
                                            $currencies = App\Models\Currency::all();
                                        @endphp
                                         <tr>
                                            <td class="text-center"> {{ $horse->transporter ? $horse->transporter->name : "" }} </td>
                                            <td class="text-center">{{ $horse->horse_number}} </td>
                                            <td class="text-center">{{ $horse->horse_make ? $horse->horse_make->name : ""}}</td>
                                            <td class="text-center">{{ $horse->horse_model ? $horse->horse_model->name : ""}}</td>
                                            <td class="text-center"> {{$horse->registration_number}}</td>
                                            <td class="text-center">
                                                @foreach ($currencies as $currency)
                                                        @php
                                                            $revenue = App\Models\Trip::where('horse_id',$horse->id)
                                                                                        ->where('currency_id',$currency->id)->sum('freight');
                                                        @endphp
                                                        @if (isset($revenue) && $revenue > 0)
                                                            {{ $currency->name }} {{ $currency->symbol }}{{number_format($revenue,2)}} <br>
                                                        @endif
                                                    @endforeach
                                            </td>
                                        </tr>
        
                                    @endforeach
                                   
                                </tbody>
                        
                            </table>
                           
                        </main>
                        @elseif ($selectedFilter == "fuel")
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to" >
                                   
                                </div>
                                <div class="col invoice-details">
                                    <h4 class="to" style="margin-bottom: -1px;">Fuel Usage Report</h4>
                                    @if (isset($from) && isset($to))
                                    <div class="date" style="padding-bottom: 3px"> <strong>From: </strong>{{ date('F j, Y', strtotime($from)) }}</div>
                                    <div class="date" style="padding-bottom: 3px"> <strong>To: </strong>{{ date('F j, Y', strtotime($to)) }}</div>
                                    @endif
                                    
                                    <hr>
                                </div>
                            </div> 
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"><strong>Transporter</strong></th>
                                        <th class="text-center"> <strong>Horse#</strong></th>
                                        <th class="text-center"> <strong>Make</strong></th>
                                        <th class="text-center"> <strong>Model</strong></th>
                                        <th class="text-center"><strong>HRN</strong></th>
                                        <th class="text-center"><strong>Fuel</strong></th>
                                </thead>
                                <tbody>
                                    @foreach ($horses as $horse)
                                 
                                     <tr>
                                        <td class="text-center"> {{ $horse->transporter ? $horse->transporter->name : "" }} </td>
                                        <td class="text-center">{{ $horse->horse_number}} </td>
                                        <td class="text-center">{{ $horse->horse_make ? $horse->horse_make->name : ""}}</td>
                                        <td class="text-center">{{ $horse->horse_model ? $horse->horse_model->name : ""}}</td>
                                        <td class="text-center"> {{$horse->registration_number}}</td>
                                        <td class="text-center">{{$horse->fuels->sum('quantity')}} Litres</td>
                                    </tr>
        
                                @endforeach
                                </tbody>
                            </table>
                           
                        </main>
                        @endif
                     {{-- <center> <footer>{{$company->invoice_footer}}</footer></center>   --}}
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('extra-js')
<script>
    window.addEventListener("load", window.print());
    </script>
@endsection
