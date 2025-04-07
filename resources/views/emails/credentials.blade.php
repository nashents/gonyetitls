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
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated account creation email</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Congratulations your {{ ucfirst($user->category) }} account has been created successfully. Please verify if your details below are correct.</p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><strong>Name:</strong> {{ucfirst($user->name)}},
													@if (isset($user->surname))
													<strong>Surname:</strong> {{ucfirst($user->surname)}},
													@else
													@endif
													<strong>Email:</strong>{{$user->email}}
													<br>
												<strong>Username:</strong>  {{$user->username}}, <strong>Pin:</strong> {{$pin}}</p>
												<br>
												<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><a href="{{ $company->website }}" style="color:#fcc02d;text-decoration:underline;" target="_blank">Click me to get started</a></p>

												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color: red">Note: your password is your secret. You can change this password to your new desired password in your dashboard under account.</p>
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
