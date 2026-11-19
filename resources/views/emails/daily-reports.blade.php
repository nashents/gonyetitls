@extends('layouts.emails')
@section('title')
Daily Reports | {{$company->name}}
@endsection
@section('heading')
Daily Reports
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">{{ $subjectLine }}</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Please find attached the daily reports.</p>
											</td>
										</tr>

									</table>
</td></tr>
</table>
@endsection
