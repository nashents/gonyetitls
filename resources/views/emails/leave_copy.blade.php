@extends('layouts.emails')
@section('title')
Leave Copy | {{$company->name}}
@endsection
@section('heading')
Leave Copy
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
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
</td></tr>
</table>
@endsection
