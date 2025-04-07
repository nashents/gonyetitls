@extends('layouts.previews')
@section('title')
    Trip Sheet Preview|@if (isset(Auth::user()->employee->company))
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
                {{-- <div class="toolbar hidden-print">
                    <div class="text-end">
                        <button type="button" onclick="goBack()" class="btn btn-default" ><i class="fa fa-arrow-left"></i> Back</button>
                        <a href="{{route('trips.print',$trip->id)}}" class="btn btn-dark"><i class="fa fa-print"></i> Print</a>
                    </div>
                    <hr>
                </div> --}}
                <div class="invoice overflow-auto">
                    <div style="min-width: 600px">
                        <header>
                            <div class="row">
                                <div class="col">
                                    <a href="javascript:;">
    									<img src="{{asset('images/uploads/'.$company->logo)}}" width="80" alt="">
									</a>
                                </div>
                                <div class="col company-details">
                                    <h4 class="name">

                                            {{-- SubHeading {{$quotation->subheading}} --}}

                                    </h4>
                                    <h2 class="name">
                                        <a target="_blank" href="javascript:;">
									{{$company->name}}
									</a>
                                    </h2>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                    <div>{{$company->phonenumber}}
                                    </div>
                                    <div>{{$company->email}}</div>
                                </div>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">Customer: {{$customer->name}}</div>
                                    <h4 class="to">Contact: {{$customer->contact_name}} {{$customer->contact_name}}</h4>
                                    <div class="email"><a href="mailto:{{$customer->email}}">Email: {{$customer->email}}, Phonenumber: {{$customer->phonenumber}}</a>
                                    <div class="address">{{$customer->street_address}} {{$customer->suburb}}, {{$customer->city}} {{$customer->country}}</div>
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <h1 class="invoice-id">Trip # {{$trip->trip_number}}</h1>
                                    <div class="date">Trip Date: {{$trip->start_date}}</div>
                                </div>
                            </div>
                            <table>

                                <tbody>
                                    <tr>
                                        <th class="text-center"><strong>Trip #</strong></th>
                                        <td class="no text-center">{{$trip->trip_number}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Trip Type</strong></th>
                                        <td class="text-center"> {{$trip->trip_type ? $trip->trip_type->name : "undefined trip type"}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>Horse</strong></th>
                                        <td class="text-center">{{$trip->horse ? $trip->horse->make : "No horse assigned"}} {{$trip->horse ? $trip->horse->model : ""}} ({{$trip->horse ? $trip->horse->registration_number : ""}})</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>Trailers</strong></th>
                                        <td class="text-center">@foreach ($trip->trailers as $trailer)
                                            <ol>{{$trailer->make}}, {{$trailer->model}}, ({{$trailer->registration_number}})</ol> <br>
                                        @endforeach</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Driver</strong></th>
                                        <td class="text-center">{{$trip->driver ? $trip->driver->employee->name : ""}} {{$trip->driver ? $trip->driver->employee->surname : "Driver not assigned"}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>Start Date</strong></th>
                                        <td class="text-center"> {{$trip->start_date}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>End Date</strong></th>
                                        <td class="text-center"> {{$trip->end_date}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>Destination</strong></th>
                                        <td class="text-center"> From: {{App\Models\Destination::find($trip->from)->country ? App\Models\Destination::find($trip->from)->country->name : "undefined country"}}, {{App\Models\Destination::find($trip->from) ? App\Models\Destination::find($trip->from)->city : ""}}  To: {{App\Models\Destination::find($trip->from)->country ? App\Models\Destination::find($trip->to)->country->name : ""}}, {{App\Models\Destination::find($trip->from) ? App\Models\Destination::find($trip->to)->city : ""}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>Loading Point</strong></th>
                                        <td class="text-center">{{$trip->loading_point ? $trip->loading_point->name : "No loading point selected"}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"> <strong>Offloading Point</strong></th>
                                        <td class="text-center">{{$trip->offloading_point ? $trip->offloading_point->name : "No offloading point selected"}}</td>
                                    </tr>

                                    <tr>
                                        <th class="text-center"><strong>Cargo</strong></th>
                                        <td class="text-center"> {{$trip->cargo ? $trip->cargo->group : "No cargo selected"}}: {{$trip->cargo ? $trip->cargo->name : ""}}, measured in {{$trip->cargo ? $trip->cargo->measurement : ""}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Quantity</strong></th>
                                        <td class="text-center"> {{$trip->quantity}} {{$trip->measurement}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Weight</strong></th>
                                        <td class="text-center"> {{$trip->weight}} Tons</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Currency</strong></th>
                                        <td class="text-center"> {{$trip->currency ? $trip->currency->name : "no currency selected"}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Rate</strong></th>
                                        <td class="text-center">${{number_format($trip->rate,2)}} per unit</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Freight</strong></th>
                                        <td class="text-center"> ${{number_format($trip->freight,2)}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Trip Status</strong></th>
                                        <td class="text-center"> {{$trip->trip_status}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Payment Status</strong></th>
                                        <td class="text-center"> {{$trip->payment_status}}</td>
                                    </tr>
                                    @if ($trip->comments != "")
                                    <tr>
                                        <th class="text-center"><strong>Comments</strong></th>
                                        <td class="text-center"> {{$trip->comments}}</td>
                                    </tr>
                                    @endif
                                    @if (isset($trip->broker))
                                    <tr>
                                        <th class="text-center"><strong>Broker</strong></th>
                                        <td class="text-center"> {{$trip->broker->name}}, Email: {{$trip->broker->email}}, Phonenumber: {{$trip->broker->phonenumber}}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center"><strong>Checked By</strong></th>
                                        <td class="text-center"> {{$trip->user->employee ? $trip->user->employee->name : "Not defined" }} {{$trip->user->employee ? $trip->user->employee->surname : "Not defined" }}</td>
                                    </tr>

                                </tbody>

                            </table>
                        </main>
                        <footer>{{ucfirst($company->name)}} Trip Sheet {{$trip->trip_number}}!!</footer>
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('extra-js')
<script>
    window.addEventListener("load", window.print());
  </script>
@endsection
@endsection
