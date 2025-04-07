@extends('layouts.emails')
@section('title')
Account Creation | {{$company->name}}
@endsection
@section('content')

	<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
		<tr>
			<td align="center" style="padding:0;">
				<table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
					<tr>
						<td align="center" style="padding:40px 0 30px 0;background:{{$company->color}};">
                            <img src="#" alt="" width="300" style="height:auto;display:block;" />
                        </td>
					</tr>
					<tr>
						<td style="padding:36px 30px 42px 30px;">
							<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{$company->name}}</h3>
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
						</td>
					</tr>
					<tr>
						<td style="padding:30px;background: {{$company->color}};">
							<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
								<tr>
									<td style="padding:0;width:50%;" align="left">
									
											<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">Powered By</p> 
											<br>
										<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
											&reg;
											Gonyeti TLS {{date('Y')}} | <a href="mailto:info@gonyetitls.com" style="color:#ffffff;text-decoration:underline;">info@gonyetitls.com</a>
										</p>
									
									</td>
									<td style="padding:0;width:50%;" align="right">
										<table role="presentation" style="border-collapse:collapse;border:0;border-spacing:0;">
											<tr>
												<td style="padding:0 0 0 10px;width:38px;">
													<a target="_blank" href="https://www.facebook.com/gonyetitls" style="color:#ffffff;"><img src="https://assets.codepen.io/210284/fb_1.png" alt="Facebook" width="38" style="height:auto;display:block;border:0;"  /></a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

@endsection
