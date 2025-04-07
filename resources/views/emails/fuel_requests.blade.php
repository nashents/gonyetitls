@extends('layouts.emails')
@section('title')
Fuel Request | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection
@section('content')

@endsection
	<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
		<tr>
			<td align="center" style="padding:0;">
				<table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
					<tr>
						<td align="center" style="padding:40px 0 30px 0;background:#d1281d;">
							<img src="{{asset('http://wwww.tinmac.com/images/logo.png')}}" alt="" width="300" style="height:auto;display:block;" />
						</td>
					</tr>
					<tr>
						<td style="padding:36px 30px 42px 30px;">
							<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
								<tr>
									<td style="padding:0 0 36px 0;color:#153643;">
										<h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;">Tinmac</h1>
										<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated fuel request with the following details.</p>
									</td>
								</tr>
                                <tr>
                                    <td>
                                        <h3 style="margin:0;font-size:17px;line-height:24px;font-family:Arial,sans-serif;">{{$subject}}</h3>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">{{$supplier}}</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Can you provide {{$driver}} with {{$fuel_type}} of {{$quantity}} litres</p>
                                        <p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Fuel Request Authorized By {{$authorized_by}} On: {{$date}}</p>
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
										<center>
											<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">Powered By</p> 
											<br>
										<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
											&reg;
                                            Gonyeti TLS {{date('Y')}}<br/>
                                        <a href="mailto:info@gonyetitls.com" style="color:#ffffff;text-decoration:underline;">
                                            info@gonyetitls.com</a>
										</p>
									</center>
									</td>
									<td style="padding:0;width:50%;" align="right">
										<table role="presentation" style="border-collapse:collapse;border:0;border-spacing:0;">
											<tr>
												</td>
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
</body>
</html>
