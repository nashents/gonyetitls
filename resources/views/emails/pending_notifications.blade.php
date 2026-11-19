@extends('layouts.emails')
@section('title')
Pending Authorization | {{$company->name}}
@endsection
@section('heading')
{{ trim(($notification?->when == 'Before' ? 'Pending ' : '') . ($notification?->category ?? 'Authorization')) }}
@endsection
@section('content')
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
<tr><td style="padding:0;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated {{$notification?->when == "Before" ? "Pending" : ""}} {{ $notification?->category }} Email</p>
											</td>
										</tr>
										<tr>
											<td>
												@php
													// Normalize once: works for "https://gonyetitls.test" and "https://gonyetitls.test/"
													$baseUrl = rtrim($company->website ?? '', '/');
												@endphp

												@if ($notification->category == "Trip Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize trip {{ $model->trip_number }}.
														<a href="{{ $baseUrl }}/trips/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>

												@elseif ($notification->category == "Requisition Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize requisition {{ $model->requisition_number }}.
														<a href="{{ $baseUrl }}/requisitions/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>

												@elseif ($notification->category == "Credit Note Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize credit note {{ $model->credit_note_number }}.
														<a href="{{ $baseUrl }}/credit_notes/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>

												@elseif ($notification->category == "Fuel Order Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize fuel order {{ $model->order_number }}.
														<a href="{{ $baseUrl }}/fuels/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>

												@elseif ($notification->category == "Garage Booking Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize booking {{ $model->order_number }}.
														<a href="{{ $baseUrl }}/bookings/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>

												@elseif ($notification->category == "Invoice Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize invoice {{ $model->invoice_number }}.
														<a href="{{ $baseUrl }}/invoices/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>
												@elseif ($notification->category == "Dispatch Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize dispatch {{ $model->dispatch_number }}.
														<a href="{{ $baseUrl }}/dispatches/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>
												@elseif ($notification->category == "Fuel Top Up Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize fuel top-up {{ $model->order_number }}.
														<a href="{{ $baseUrl }}/top_ups/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>
												@elseif ($notification->category == "Purchase Order Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize purchase order {{ $model->purchase_number }}.
														<a href="{{ $baseUrl }}/purchases/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>
												@elseif ($notification->category == "Fuel Requests Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize fuel request {{ $model->request_number }}.
														<a href="{{ $baseUrl }}/fuel_requests/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>
												@elseif ($notification->category == "Freight Job Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize freight job {{ $model->job_number }}{{ $model->customer ? ' for '.$model->customer->name : '' }}.
														<a href="{{ $baseUrl }}/freight/jobs/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>
												@endif
												<br>
											</td>
										</tr>

									</table>
</td></tr>
</table>
@endsection
