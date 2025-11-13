@extends('layouts.emails')
@section('title')
Reminder | {{$company->name}}
@endsection
@section('content')

	<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
		<tr>
			<td align="center" style="padding:0;">
				<table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
					<tr>
						<td align="center" style="padding:40px 0 30px 0;background:{{$company->logo}};">
							<img src="{{$company->website.'/images/uploads/'.$company->logo}}" alt=""  style="display:block;" />
						</td>
					</tr>
					<tr>
						<td style="padding:36px 30px 42px 30px;">
							<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{$company->name}}</h3>
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated reminder email</p>
									</td>
								</tr>
                                <tr>
                                    <td>
                                        @if ($fitness->horse)
                                        	<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your horse <strong>{{$fitness->name}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											 </p>
                                        	<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												    for <strong> {{$fitness->horse->horse_make->name ?? ''}} {{$fitness->horse->horse_model->name ?? ''}} </strong> with registration number 
														<strong>
															{{$fitness->horse->registration_number}} 
															{{ $fitness->horse->fleet_number ? '(' . $fitness->horse->fleet_number . ')' : '' }}
														</strong>
													is about to expire.
											</p>
                                        @endif
                                        @if ($fitness->vehicle)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your vehicle <strong>{{$fitness->name}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												 	for <strong> {{$fitness->vehicle->vehicle_make->name ?? ''}} {{$fitness->vehicle->vehicle_model->name ?? ''}} </strong> with registration number 
												  		<strong>
															{{$fitness->vehicle->registration_number}} 
															{{ $fitness->vehicle->fleet_number ? '(' . $fitness->vehicle->fleet_number . ')' : '' }}
														</strong>
													is about to expire.
											</p>
										@endif
                                        @if ($fitness->trailer)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your trailer <strong>{{$fitness->name}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
												 	for <strong> {{$fitness->trailer->trailer_make->name ?? ''}} {{$fitness->trailer->trailer_model->name ?? ''}} </strong> with registration number 
												  		<strong>
															{{$fitness->trailer->registration_number}} 
															{{ $fitness->trailer->fleet_number ? '(' . $fitness->trailer->fleet_number . ')' : '' }}
														</strong>
													is about to expire.
											</p>
										@endif
                                        @if ($fitness->employee)
											<p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">
												Your <strong>{{$fitness->name}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}},
											</p>
											<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													for <strong>
														 	{{ucfirst($fitness->employee->name)}} {{ucfirst($fitness->employee->surname)}}
														</strong>
													is about to expire.
											</p>
                                        @endif
                                        
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
											Renew your <strong>{{$fitness->name}}</strong> before
											<strong style="color: red">{{Carbon\Carbon::parse($fitness->expires_at)->format('d-m-Y')}}</strong> to avoid any inconveniences. <a href="{{$company->website}}/fitnesses/{{$fitness->id}}" target="_blank" style="color: blue"> Click me to take action</a>
										</p>
                                    </td>
                                </tr>

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
											Gonyeti TLS {{date('Y')}} | <a href="mailto:info@basilmark.com" style="color:#ffffff;text-decoration:underline;">info@basilmark.com</a>
										</p>
									
									</td>
									<td style="padding:0;width:50%;" align="right">
										<table role="presentation" style="border-collapse:collapse;border:0;border-spacing:0;">
											<tr>
												<td style="padding:0 0 0 10px;width:38px;">
													<a target="_blank" href="https://www.facebook.com/basilmarkzw" style="color:#ffffff;"><img src="https://assets.codepen.io/210284/fb_1.png" alt="Facebook" width="38" style="height:auto;display:block;border:0;"  /></a>
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
