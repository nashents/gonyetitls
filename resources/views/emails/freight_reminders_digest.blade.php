@extends('layouts.emails')
@section('title')
Freight Reminders | {{$company->name}}
@endsection
@section('content')
@php
    $groups = [
        'overdue' => ['label' => 'Overdue', 'color' => '#c0392b'],
        'today' => ['label' => 'Due today', 'color' => '#d35400'],
        'upcoming' => ['label' => 'Coming up', 'color' => '#2471a3'],
    ];
    $bySeverity = collect($reminders)->groupBy('severity');
@endphp
			<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
				<tr>
					<td align="center" style="padding:0;">
						<table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
							<tr>
								<td align="center" style="padding:30px 0 20px 0;background:{{$company->color}};">
									<h2 style="margin:0;color:#ffffff;font-family:Arial,sans-serif;">Freight Reminders</h2>
								</td>
							</tr>
							<tr>
								<td style="padding:30px 30px 36px 30px;font-family:Arial,sans-serif;color:#153643;">
									<h3 style="font-size:16px;margin:0 0 12px 0;">{{$company->name}}</h3>
									<p style="margin:0 0 20px 0;font-size:14px;line-height:22px;">
										These freight operations need attention as of {{ now()->format('d M Y') }}.
									</p>

									@foreach ($groups as $severity => $meta)
										@if ($bySeverity->has($severity))
											<p style="margin:16px 0 6px 0;font-size:14px;font-weight:bold;color:{{ $meta['color'] }};">
												{{ $meta['label'] }} ({{ $bySeverity[$severity]->count() }})
											</p>
											<table role="presentation" style="width:100%;border-collapse:collapse;font-size:13px;">
												@foreach ($bySeverity[$severity] as $reminder)
													<tr>
														<td style="padding:6px 8px;border-bottom:1px solid #eeeeee;white-space:nowrap;vertical-align:top;">
															<a href="{{ $reminder['url'] }}" target="_blank" style="color:blue;">{{ $reminder['job_number'] }}</a>
														</td>
														<td style="padding:6px 8px;border-bottom:1px solid #eeeeee;">{{ $reminder['message'] }}</td>
													</tr>
												@endforeach
											</table>
										@endif
									@endforeach

									<p style="margin:24px 0 0 0;font-size:12px;color:#777777;">
										You get this email because you created or are an officer on these freight jobs, or are set up to receive Freight Reminders. It is sent at most once a day.
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
@endsection
