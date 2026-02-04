@extends('layouts.emails')
@section('title')
Account Creation | {{$company->name}}
@endsection
@section('content')

			<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
				<tr>
					<td align="center" style="padding:0;">
						<table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
			
							<tr>
								<td align="center" style="padding:40px 0 30px 0;background:{{$company->color}};">
									<img src="#" alt="" width="300" style="height:auto;display:block;" />
								</td>
							</tr>
							<tr>
								<td style="padding:36px 30px 42px 30px;">
									<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
										<tr>
											<td style="padding:0 0 15px 0;color:#153643;">
												<h3 style="font-size:16px; margin:0 0 20px 0;font-family:Arial,sans-serif;">{{$company->name}}</h3>
												<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">This is an automated {{ $notification->category }} Email</p>
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

												@elseif ($notification->category == "Purchase Order Authorization")
													<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
														Can you please authorize purchase order {{ $model->purchase_number }}.
														<a href="{{ $baseUrl }}/purchases/{{ $model->id }}" target="_blank" style="color: blue">Click me to authorize</a>
													</p>

												@elseif ($notification->category == "Reminder Notification")
													{{-- ... --}}
												@endif
												<br>
											</td>
										</tr>

									</table>
								</td>
							</tr>
							<tr>
								<td style="padding:30px;background: {{$company->color}};">
									<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
										<tr>
											<td style="padding:0;width:50%;" align="left">
											
													<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">Powered By</p> 
													<br>
												<p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
													&reg;
													Gonyeti TLS {{date('Y')}} | <a href="mailto:info@basilmark.com" style="color:#ffffff;text-decoration:underline;">info@basilmark.com</a>
												</p>
											
											</td>
											<td style="padding:0;width:50%;" align="right">
												<table role="presentation" style="border-collapse:collapse;border:0;border-spacing:0;">
													<tr>
														<td style="padding:0 0 0 10px;width:38px;">
															<a target="_blank" href="https://www.facebook.com/basilmarkzw" style="color:#ffffff;"><img src="https://assets.codepen.io/210284/fb_1.png" alt="Facebook" width="38" style="height:auto;display:block;border:0;"  /></a>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>


@endsection
