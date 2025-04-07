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
						<td align="center" style="padding:40px 0 30px 0;background:{{$company->color}};">
                            <img src="{{asset('images/gonyeti-mini.png')}}" alt="" width="300" style="height:auto;display:block;" />
                        </td>
					</tr>
					<tr>
						<td style="padding:36px 30px 42px 30px;">
							<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 15px 0;color:#153643;">
										<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{$company->name}}</h3>
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated fitness reminder email</p>
									</td>
								</tr>
                                <tr>
                                    <td>

                                        @if ($fitness->horse)
                                        <p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">Your horse <strong>{{$fitness->name}}</strong>  issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}}, </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">for <strong>{{$fitness->horse->horse_make ? $fitness->horse->horse_make->name : "Horse make & model undefined"}} {{$fitness->horse->horse_model ? $fitness->horse->horse_model->name : ""}}</strong> with registration number <strong>{{$fitness->horse->registration_number}}</strong>  is about to expire. </p>
                                        @endif
                                        @if ($fitness->vehicle)
                                        <p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">Your motor vehicle <strong>{{$fitness->name}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}} </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">for  <strong> {{$fitness->vehicle->vehicle_make ? $fitness->vehicle->vehicle_make->name : "Vehicle make & model undefined"}} {{$fitness->vehicle->vehicle_model ? $fitness->vehicle->vehicle_model->name : ""}} </strong> with registration number <strong>{{$fitness->vehicle->registration_number}}</strong> </p>
                                        @endif
                                        @if ($fitness->trailer)
                                        <p style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">Your trailer <strong>{{$fitness->name}}</strong> issued on {{Carbon\Carbon::parse($fitness->issued_at)->format('d-m-Y')}} </p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">for  <strong> {{$fitness->trailer->make}} {{$fitness->trailer->model}} </strong> with registration number <strong> {{$fitness->trailer->registration_number}}</strong></p>
                                        @endif
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Renew your <strong>{{$fitness->name}}</strong> before <strong style="color: red">{{Carbon\Carbon::parse($fitness->expires_at)->format('d-m-Y')}}</strong> to avoid any inconveniences.</p>
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
