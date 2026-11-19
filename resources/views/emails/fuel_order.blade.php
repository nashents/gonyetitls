@extends('layouts.emails')
@section('title')
Fuel Order | {{$company->name}}
@endsection
@section('heading')
Fuel Order
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated fuel order notification</p>
									</td>
								</tr>
								<tr>
                                    <td>
                                        <h5 style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">{{$station_name}}</h5>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Fuel Order #:</strong> {{$order_number}}</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Can you provide {{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}} <i>{{$driver->employee ? $driver->employee->idnumber : ""}}</i> with {{$quantity}} litres of {{$fuel_type}} fuel. </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">{{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}} HRN: {{ucfirst($horse->registration_number)}}.</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Enroute:</strong> From: {{ucfirst($collection_point)}} To: {{ucfirst($delivery_point)}}.</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Authorized By: </strong>  <i>{{$authorized_by}} On: {{$date}}</i> </p>
                                    </td>
                                </tr>
                               

							</table>
</td></tr>
</table>
@endsection
