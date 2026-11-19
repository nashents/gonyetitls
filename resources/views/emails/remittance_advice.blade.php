@extends('layouts.emails')
@section('title')
Remittance Advice | {{$company->name}}
@endsection
@section('heading')
Remittance Advice
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">Dear {{$vendor->name}}</h3>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Please find attached the remittance advice for payment {{$payment->payment_number}}, confirming the bill(s) this payment has settled.</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Should you have any queries, please contact us on: {{$company->phonenumber}} {{$company->second_phonenumber ? " / ".$company->second_phonenumber : ""}}, email: {{$company->email}} for an accurate response.
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
