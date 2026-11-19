@extends('layouts.emails')
@section('title')
Account Creation | {{$company->name}}
@endsection
@section('heading')
Your Account
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated account credentials email</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Congratulations! Your {{ ucfirst($user->category) }} account details are ready. Please review the information below to ensure everything is correct.</p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													<strong>Name:</strong> {{ucfirst($user->name)}},
													@if (isset($user->surname))
														<strong>Surname:</strong> {{ucfirst($user->surname)}},
													@endif
													<strong>Email:</strong>{{$user->email}}
													<br>
												<strong>Username:</strong>  {{$user->username}}, <strong>Pin:</strong> {{$pin}}</p>
												<br>
												<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"><a href="{{ $company->website }}" style="color:#fcc02d;text-decoration:underline;" target="_blank">Click me to get started</a></p>

												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;color: red">Note: Your PIN is confidential. You may change it to a new one anytime from your dashboard under Profile Settings.</p>
												@if (!empty($shared_by_name))
													<p style="margin:0 0 12px 0;font-size:14px;line-height:20px;font-family:Arial,sans-serif;color:#555555;"><strong>Shared by:</strong> {{ $shared_by_name }}</p>
												@endif
											</td>
										</tr>

									</table>
</td></tr>
</table>
@endsection
