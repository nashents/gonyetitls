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
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Hello {{ $user->name }} </p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Someone requested a link to change your password. Click the button below to proceed.</p>
												<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <a href="{{ $link }}" class="btn btn-success">Update my password</a></p>
												<br>
												<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">You can also copy and paste this URL into your web browser:</p>
												<p style="margin:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;"> <a href="{{ $link }}" target="_blank" >{{ $link }}</a></p>
												<br>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">If you didn’t request this, please ignore the email. Your password will stay safe and won’t be changed.</p>
											</td>
										</tr>

									</table>
</td></tr>
</table>
@endsection
