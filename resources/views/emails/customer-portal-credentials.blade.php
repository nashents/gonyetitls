@extends('layouts.emails')
@section('title')
Freight Portal Login | {{$company->name}}
@endsection
@section('content')

			<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
				<tr>
					<td align="center" style="padding:0;">
						<table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">

							<tr>
								<td align="center" style="padding:40px 0 30px 0;background:{{$company->color}};">
									<img src="{{$company->website.'/images/uploads/'.$company->logo}}" alt=""  style="display:block;" />
								</td>
							</tr>
							<tr>
								<td style="padding:36px 30px 42px 30px;">
									<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{$company->name}}</h3>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated freight portal login email</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Hi {{ ucfirst($customer->name) }}, you can now track your freight jobs and trips online. Use the details below to log in.</p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													<strong>Email:</strong> {{ $customer->email }}
													<br>
													<strong>PIN:</strong> {{ $pin }}</p>
												<br>
												<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><a href="{{ $loginUrl }}" style="color:#fcc02d;text-decoration:underline;" target="_blank">Click me to log in</a></p>

												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color: red">Note: This PIN is confidential. Contact us if you did not request it.</p>
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
													Gonyeti T & L ERP {{date('Y')}} | <a href="mailto:info@basilmark.com" style="color:#ffffff;text-decoration:underline;">info@basilmark.com</a>
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
