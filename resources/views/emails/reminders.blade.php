@extends('layouts.emails')
@section('title')
Reminder | {{$company->name}}
@endsection
@section('heading')
Expiry Reminder
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated reminder email</p>
									</td>
								</tr>
                                <tr>
                                    <td>
                                        @if ($fitness->horse)
                                        	<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your horse <strong>{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											 </p>
                                        	<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												    for <strong> {{$fitness->horse->horse_make->name ?? ''}} {{$fitness->horse->horse_model->name ?? ''}} </strong> with registration number 
														<strong>
															{{$fitness->horse->identifier_label}}
														</strong>
													is about to expire.
											</p>
                                        @endif
                                        @if ($fitness->vehicle)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your vehicle <strong>{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												 	for <strong> {{$fitness->vehicle->vehicle_make->name ?? ''}} {{$fitness->vehicle->vehicle_model->name ?? ''}} </strong> with registration number 
												  		<strong>
															{{$fitness->vehicle->identifier_label}}
														</strong>
													is about to expire.
											</p>
										@endif
                                        @if ($fitness->trailer)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your trailer <strong>{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												 	for <strong> {{$fitness->trailer->trailer_make->name ?? ''}} {{$fitness->trailer->trailer_model->name ?? ''}} </strong> with registration number 
												  		<strong>
															{{$fitness->trailer->identifier_label}}
														</strong>
													is about to expire.
											</p>
										@endif
                                        @if ($fitness->employee)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your <strong>{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													for <strong>
														 	{{ucfirst($fitness->employee->name)}} {{ucfirst($fitness->employee->surname)}}
														</strong>
													is about to expire.
											</p>
                                        @endif
                                        @if ($fitness->customer || $fitness->vendor)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												The <strong>{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												for {{ $fitness->customer ? 'customer' : 'vendor' }} <strong>{{ $fitness->customer ? $fitness->customer->name : $fitness->vendor->name }}</strong>
												is about to expire.
											</p>
                                        @endif
                                        
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
											Renew your <strong>{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</strong> before
											<strong style="color: red">{{Carbon\Carbon::parse($fitness->expires_at)->format('d-m-Y')}}</strong> to avoid any inconveniences. <a href="{{$company->website}}/fitnesses/{{$fitness->id}}" target="_blank" style="color: blue"> Click me to take action</a>
										</p>
                                    </td>
                                </tr>

							</table>
</td></tr>
</table>
@endsection
