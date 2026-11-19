@extends('layouts.emails')
@section('title')
Freight Portal Login | {{$company->name}}
@endsection
@section('heading')
Freight Portal Login
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												
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
</td></tr>
</table>
@endsection
