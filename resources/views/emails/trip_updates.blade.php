@extends('layouts.emails')
@section('title')
Account Creation | {{$company->name}}
@endsection
@section('heading')
Trip Update
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated trip status updates email</p>
									</td>
								</tr>
                                <tr>
									<th><h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">Customer</h3></th>
                                    <td>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">{{ucfirst($customer->name)}},</p>
                                    </td>
                                </tr>
								@if (isset($trips))
								@foreach ($trips as $trip)
								<tr>
									<th><h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{ $trip->trip_number }}</h3></th>
                                    <td>
										@php
											if ($trip->horse) {
												$make = $trip->horse->horse_make ? $trip->horse->horse_make->name : ""; 
												$model = $trip->horse->horse_model ? $trip->horse->horse_model->name : ""; 
												$regnumber = $trip->horse ? $trip->horse->registration_number : ""; 
											}
											if ($trip->driver) {
											$name = $trip->driver->employee ? $trip->driver->employee->name : "";
											$surname = $trip->driver->employee ? $trip->driver->employee->surname : "";
											$idnumber = $trip->driver->employee ? $trip->driver->employee->idnumber : "";
											}
											
										@endphp
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Trip Status: </strong> {{ $trip->trip_status }}</p>
										@if ($trip->trip_status_description)
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Status Description: </strong> {{ $trip->trip_status_description }}</p>
										@endif
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>From: </strong> {{ $origin_destination }}, <strong>To: </strong>{{ $final_destination }}, <strong>Loading Point: </strong>{{ $loading_point ? $loading_point->name : "" }}, <strong>Offloading Point: </strong>{{ $offloading_point ? $offloading_point->name : "" }}, <strong>Route: </strong>{{ $trip->route ? $trip->route->name: "" }}</p>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Horse Details: </strong>{{ $make }} {{ $model }} <i>{{ $regnumber }}</i></p>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Driver Details: </strong>  {{ $name }} {{ $surname }} <i>{{ $idnumber }}</i></p>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Cargo: </strong> {{ $cargo->name }}, <strong>Weight: </strong>{{ $trip->weight }}Tons, 
											@if (isset($trip->quantity))
											<strong>Quantity: </strong>{{ $trip->quantity }}
											@else 
											<strong>Litreage: </strong>{{ $trip->litreage }}
											@endif
											{{ $trip->measurement }} </p>
                                    </td>
                                </tr>
								<br>
								<br>
								@endforeach
								@else   
								<tr>
									<th><h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{ $trip->trip_number }}</h3></th>
                                    <td>
										@php
											if ($trip->horse) {
												$make = $trip->horse->horse_make ? $trip->horse->horse_make->name : ""; 
												$model = $trip->horse->horse_model ? $trip->horse->horse_model->name : ""; 
												$regnumber = $trip->horse ? $trip->horse->registration_number : ""; 
											}
											if ($trip->driver) {
											$name = $trip->driver->employee ? $trip->driver->employee->name : "";
											$surname = $trip->driver->employee ? $trip->driver->employee->surname : "";
											$idnumber = $trip->driver->employee ? $trip->driver->employee->idnumber : "";
											}
											
										@endphp
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Trip Status: </strong> {{ $trip->trip_status }}</p>
										@if ($trip->trip_status_description)
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Status Description: </strong> {{ $trip->trip_status_description }}</p>
										@endif
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>From: </strong> {{ $origin_destination }}, <strong>To: </strong>{{ $final_destination }}, <strong>Loading Point: </strong>{{ $loading_point->name }}, <strong>Offloading Point: </strong>{{ $offloading_point->name }}, <strong>Route: </strong>{{ $trip->route ? $trip->route->name: "" }}</p>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Horse Details: </strong>{{ $make }} {{ $model }} <i>{{ $regnumber }}</i></p>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Driver Details: </strong>  {{ $name }} {{ $surname }} <i>{{ $idnumber }}</i></p>
                                        <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Cargo: </strong> {{ $cargo->name }}, <strong>Weight: </strong>{{ $trip->weight }}Tons, 
											@if (isset($trip->quantity))
											<strong>Quantity: </strong>{{ $trip->quantity }}
											@else 
											<strong>Litreage: </strong>{{ $trip->litreage }}
											@endif
											{{ $trip->measurement }} </p>
                                    </td>
                                </tr>

								@endif
							</table>
</td></tr>
</table>
@endsection
