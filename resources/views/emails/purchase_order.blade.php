@extends('layouts.emails')
@section('title')
Purchase Order | {{$company->name}}
@endsection
@section('heading')
Purchase Order
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">Hi {{$employee->name ? $employee->name : ""}} {{$employee->name ? $employee->surname : ""}}, </h3>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">Please find attached an approved purchase order <strong>{{$purchase->purchase_number}}</strong> to share with our supplier {{$vendor->name}}.</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													This Purchase Order was <strong>Authorized By</strong> {{$user->name}} {{$user->surname}} <strong>On:</strong> {{$purchase->authorization_date}}.
													@if ($purchase->authorization_comments)
														<br>
														With <strong>comments:</strong>  {{$purchase->authorization_comments}}
													@endif
												</p>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Should you have any questions regarding this order, please get in touch with the authorizer for clarification.
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
													Kind Regards.
												</p>
											</td>
										</tr>
										

									</table>
</td></tr>
</table>
@endsection
