@extends('layouts.emails')
@section('title')
Garage Booking | {{$company->name}}
@endsection
@section('heading')
Garage Booking
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated fuel order Vehicle Service Booking Email</p>
									</td>
								</tr>
								<tr>
                                    <td>
                                        <h5 style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">{{$booking->vendor ? $booking->vendor->name : ""}}</h5>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Booking #:</strong> {{$booking->booking_number}}</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Can you provide {{ $booking->service_type }} for {{$booking->vehicle->vehicle_make ? $booking->vehicle->vehicle_make->name : ""}} {{$booking->vehicle->vehicle_model ? $booking->vehicle->vehicle_model->name : ""}} {{$booking->vehicle ? $booking->vehicle->name : ""}} </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <strong>Requested By: </strong>  <i>{{Auth::user($booking->authorized_by_id) ? Auth::user($booking->authorized_by_id)->name : ""}} {{Auth::user($booking->authorized_by_id) ? Auth::user($booking->authorized_by_id)->surname : ""}} On: {{$booking->created_at}}</i> </p>
                                    </td>
                                </tr>
                               

							</table>
</td></tr>
</table>
@endsection
