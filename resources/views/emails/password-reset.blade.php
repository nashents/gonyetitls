@extends('layouts.emails')
@section('title')
Password Reset | {{$company->name}}
@endsection
@section('heading')
Password Reset
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated password reset email</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Congratulations your {{ ucfirst($user->category) }} account pin reset was successfully. Please verify if your details below are correct.</p>
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
</td></tr>
</table>
@endsection
