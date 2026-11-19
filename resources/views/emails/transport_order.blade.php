@extends('layouts.emails')
@section('title')
Transportation Order | {{$company->name}}
@endsection
@section('heading')
Transportation Order
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated transportation order notification</p>
									</td>
								</tr>
								<tr>
                                    <td>
                                        <h5 style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">{{$collection_point}}</h5>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif; padding-top:5px; "><strong>Trip#:</strong> {{$trip->trip_number}} <strong>Transporter:</strong> {{ $transporter->name }}</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif; padding-top:5px;">Can you provide {{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}} <i>{{$driver->employee ? $driver->employee->idnumber : ""}}</i> with {{ $quantity ? $quantity : $litreage  }} {{ $uom }}, {{$weight}} tons of {{$cargo}}. </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif; padding-top:5px;"><strong>Horse Details:</strong> {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}} <i>{{ucfirst($horse->registration_number)}}</i>, <strong>Trailer(s):</strong> {{ $regnumbers }}</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif; padding-top:5px;"> <strong>Enroute:</strong> From: {{ucfirst($collection_point)}} To: {{ucfirst($delivery_point)}}.</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif; padding-top:5px;"> <strong>Checked By: </strong>  <i>{{$checked_by}}</i> </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif; padding-top:5px;"> <strong>Authorized By: </strong>  <i>{{$authorized_by}} On: {{$date}}</i> </p>
                                    </td>
                                </tr>
                               

							</table>
</td></tr>
</table>
@endsection
