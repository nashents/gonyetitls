@extends('layouts.emails')
@section('title')
Authorization Notification | {{$company->name}}
@endsection
@section('heading')
{{ $notification }}
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated {{ $notification }} Email</p>
											</td>
										</tr>
										<tr>
											<td>
												@php
													// Normalize once: works for "https://gonyetitls.test" and "https://gonyetitls.test/"
													$baseUrl = rtrim($company->website ?? '', '/');
												@endphp

												@if ($notification == "Trip Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your trip {{ $model->trip_number }} has been authorized.
														<a href="{{ $baseUrl }}/trips/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Requisition Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your requisition {{ $model->requisition_number }} has been authorized.
														<a href="{{ $baseUrl }}/requisitions/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Credit Note Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your credit note {{ $model->credit_note_number }} has been authorized.
														<a href="{{ $baseUrl }}/credit_notes/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Fuel Order Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your fuel order {{ $model->order_number }} has been authorized.
														<a href="{{ $baseUrl }}/fuels/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>
												
												@elseif ($notification == "Fuel Request Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your fuel request {{ $model->request_number }} has been authorized.
														<a href="{{ $baseUrl }}/fuel_requests/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Garage Booking Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your booking {{ $model->order_number }} has been authorized.
														<a href="{{ $baseUrl }}/bookings/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Invoice Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your invoice {{ $model->invoice_number }} has been authorized.
														<a href="{{ $baseUrl }}/invoices/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Purchase Order Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your purchase order {{ $model->purchase_number }} has been authorized.
														<a href="{{ $baseUrl }}/purchases/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Goods Received Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your goods received voucher {{ $model->goods_received_number }} has been authorized.
														<a href="{{ $baseUrl }}/goods_receiveds/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>

												@elseif ($notification == "Goods Returned Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your goods return {{ $model->goods_returned_number }} has been {{ $model->authorization }}.
														<a href="{{ $baseUrl }}/goods_returneds/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>
												@elseif ($notification == "Freight Job Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Your freight job {{ $model->job_number }} has been {{ $model->authorization }}.
														@if ($model->authorization_comments)
															<br>Comment: {{ $model->authorization_comments }}
														@endif
														<a href="{{ $baseUrl }}/freight/jobs/{{ $model->id }}" target="_blank" style="color: blue">Click me to view</a>
													</p>
												@endif
												<br>
											</td>
										</tr>

									</table>
</td></tr>
</table>
@endsection
