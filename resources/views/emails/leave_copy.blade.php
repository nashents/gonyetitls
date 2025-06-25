@extends('layouts.emails')
@section('title')
Leave Copy | {{$company->name}}
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
												<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">Dear {{$leave->employee->name}} {{$leave->employee->surname}}</h3>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Please find attached your leave application copy. Kindly check if all the details are correct.</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Leave Details
												</p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Leave Application For: {{$leave->employee->name}} <br>
													Leave Created By: {{$leave->user->name}} {{$leave->user->surname}} <br>
													Leave Created On: {{$leave->created_at}} <br>
													Leave Start Date: {{$leave->from}} <br>
													Leave End Date: {{$leave->to}} <br>
													Approved Duration: {{$leave->days ? $leave->days." Days" : ""}} <br>
													Reason for leave: {{$leave->reason}} <br>
													Checked By: {{$hod->name}} {{$hod->surname}} <br>
													HOD Comments: {{$leave->hod_reply}} <br>
													Authorized By: {{$manager->name}} {{$manager->surname}} <br>
													HR Comments: {{$leave->management_reply}} <br>
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Kind Regards
													<br>
													{{$company->name}} HR Team
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
													Gonyeti {{date('Y')}} | <a href="mailto:info@basilmark.com" style="color:#ffffff;text-decoration:underline;">info@basilmark.com</a>
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
