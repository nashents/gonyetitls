@extends('layouts.emails')
@section('title')
Customer Statement | {{$company->name}}
@endsection
@section('heading')
Customer Statement
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">Dear {{$customer->name}}</h3>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Please find attached your statement. Kindly ensure all arrears are settled in due time.</p>
												<table role="presentation" style="width:100%;border-collapse:collapse;margin:8px 0 12px 0;font-family:Arial,sans-serif;font-size:14px;background:#f5f7fa;">
													<tr>
														<td style="padding:8px 12px;color:#555555;">Statement</td>
														<td style="padding:8px 12px;font-weight:bold;">{{ $type }}</td>
													</tr>
													<tr>
														<td style="padding:8px 12px;color:#555555;">Period</td>
														<td style="padding:8px 12px;font-weight:bold;">
															@if ($periodFrom && $periodTo)
																{{ date('j F Y', strtotime($periodFrom)) }} &ndash; {{ date('j F Y', strtotime($periodTo)) }}
															@else
																As of {{ date('j F Y') }}
															@endif
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Should you have any queries, please contact us on: {{$company->phonenumber}} {{$company->second_phonenumber ? " / ".$company->second_phonenumber : ""}}, email: {{$company->email}} for an accurate response.
												</p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Should you make a payment directly into any one of our bank accounts, please scan and email a copy of your deposit to our email: {{$company->email}}, stating your invoice(s) paid clearly.
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Kind Regards
													<br>
													{{$company->name}} Team
												</p>
											</td>
										</tr>

									</table>
</td></tr>
</table>
@endsection
