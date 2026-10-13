@extends('layouts.previews')
@section('title')
    Freight Job Sheet | {{ $job->job_number }}
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice">
                <div class="invoice overflow-auto">
                    <div style="min-width: 600px">
                        <header>
                            <div class="row">
                                <div class="col">
                                    <a href="javascript:;">
                                        <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                    </a>
                                </div>
                                <div class="col company-details">
                                    <div class="text-right" style="float:right; margin-left: 15px;">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode('Freight Job: '.$job->job_number) }}" alt="QR Code" width="100" height="100">
                                    </div>
                                    <h4 class="name">
                                        <a target="_blank" href="javascript:;" style="color: {{ $company->color ?? '#000' }}">
                                            {{ $company->name }}
                                        </a>
                                    </h4>
                                    <div>{{ $company->street_address }} {{ $company->suburb }} <br>
                                        {{ $company->city }}, {{ $company->country }}</div>
                                    <div>{{ $company->phonenumber }}</div>
                                    <div>{{ $company->email }}</div>
                                </div>
                            </div>
                            <div style="padding-top: 25px; padding-bottom:15px">
                                <center><h2>FREIGHT JOB SHEET</h2></center>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">CUSTOMER:</div>
                                    <h5 class="to">{{ $job->customer?->name }}</h5>
                                    <div class="address">
                                        {{ $job->customer?->street_address }} {{ $job->customer?->city }} {{ $job->customer?->country }}
                                    </div>
                                    <div class="email"><a href="mailto:{{ $job->customer?->email }}">{{ $job->customer?->email }}</a></div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date"><strong>Job Number:</strong> {{ $job->job_number }}</div>
                                    <div class="date"><strong>Customer Reference:</strong> {{ $job->customer_reference ?: '—' }}</div>
                                    <div class="date"><strong>Opened:</strong> {{ $job->opened_at ? $job->opened_at->format('d M Y') : $job->created_at->format('d M Y') }}</div>
                                </div>
                            </div>

                            <table>
                                <tbody>
                                    <tr>
                                        <th class="text-center"><strong>Service Type</strong></th>
                                        <td class="text-center">{{ $job->freight_service_type?->name ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Import / Export</strong></th>
                                        <td class="text-center">{{ ucfirst(str_replace('_', ' ', $job->import_export_type ?? '')) ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Primary Transport Mode</strong></th>
                                        <td class="text-center">{{ ucfirst($job->primary_transport_mode ?? '') ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Incoterm</strong></th>
                                        <td class="text-center">{{ \App\Models\FreightJob::INCOTERMS[$job->incoterm] ?? ($job->incoterm ?: '—') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Currency</strong></th>
                                        <td class="text-center">{{ $job->currency?->name ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Origin</strong></th>
                                        <td class="text-center">{{ $job->fromDestination ? trim(($job->fromDestination->country->name ?? '').' '.$job->fromDestination->city) : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Destination</strong></th>
                                        <td class="text-center">{{ $job->toDestination ? trim(($job->toDestination->country->name ?? '').' '.$job->toDestination->city) : '—' }}</td>
                                    </tr>
                                    @if ($job->quotation)
                                    <tr>
                                        <th class="text-center"><strong>Quotation</strong></th>
                                        <td class="text-center">{{ $job->quotation->quotation_number }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center"><strong>Salesperson</strong></th>
                                        <td class="text-center">{{ $salesperson ? $salesperson->name.' '.$salesperson->surname : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Operations Officer</strong></th>
                                        <td class="text-center">{{ $operationsOfficer ? $operationsOfficer->name.' '.$operationsOfficer->surname : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Clearing Officer</strong></th>
                                        <td class="text-center">{{ $clearingOfficer ? $clearingOfficer->name.' '.$clearingOfficer->surname : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Status</strong></th>
                                        <td class="text-center">{{ ucwords(str_replace('_', ' ', $job->status)) }}</td>
                                    </tr>
                                    @if ($job->notes)
                                    <tr>
                                        <th class="text-center"><strong>Notes</strong></th>
                                        <td class="text-center">{{ $job->notes }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>

                            @foreach ($job->shipments as $shipment)
                                <div style="padding-top: 25px; padding-bottom:10px">
                                    <h4>Shipment {{ $shipment->shipment_number }}</h4>
                                </div>
                                <table>
                                    <tbody>
                                        <tr>
                                            <th class="text-center"><strong>Mode</strong></th>
                                            <td class="text-center">{{ ucfirst($shipment->mode ?? '') ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Shipment Type</strong></th>
                                            <td class="text-center">{{ $shipment->shipment_type ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Loading Point</strong></th>
                                            <td class="text-center">{{ $shipment->loading_point?->name ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Offloading Point</strong></th>
                                            <td class="text-center">{{ $shipment->offloading_point?->name ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>ETD</strong></th>
                                            <td class="text-center">{{ $shipment->etd ? $shipment->etd->format('d M Y') : '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>ETA</strong></th>
                                            <td class="text-center">{{ $shipment->eta ? $shipment->eta->format('d M Y') : '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Booking Reference</strong></th>
                                            <td class="text-center">{{ $shipment->booking_reference ?: '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-center"><strong>Freight Terms</strong></th>
                                            <td class="text-center">{{ ucfirst($shipment->freight_terms ?? '') ?: '—' }}</td>
                                        </tr>
                                        @if ($shipment->containers->isNotEmpty())
                                        <tr>
                                            <th class="text-center"><strong>Container(s)</strong></th>
                                            <td class="text-center">
                                                @foreach ($shipment->containers as $container)
                                                    {{ $container->container_number }} ({{ $container->container_type }}) — {{ \App\Models\ShippingContainer::LIFECYCLE_STAGES[$container->status] ?? $container->status }}
                                                    @if ($container->shipping_line) via {{ $container->shipping_line->name }} @endif
                                                    <br>
                                                @endforeach
                                            </td>
                                        </tr>
                                        @endif
                                        @if ($shipment->cargo_items->isNotEmpty())
                                        <tr>
                                            <th class="text-center"><strong>Cargo</strong></th>
                                            <td class="text-center">
                                                @foreach ($shipment->cargo_items as $cargo)
                                                    {{ $cargo->commodity }} — {{ $cargo->quantity }} {{ $cargo->uom }} ({{ $cargo->gross_weight }} kg)<br>
                                                @endforeach
                                            </td>
                                        </tr>
                                        @endif
                                        @if ($shipment->parties->isNotEmpty())
                                        <tr>
                                            <th class="text-center"><strong>Parties</strong></th>
                                            <td class="text-center">
                                                @foreach ($shipment->parties as $party)
                                                    {{ $party->role ?: ucfirst(str_replace('_', ' ', $party->party_type)) }}: {{ $party->party()?->name }}<br>
                                                @endforeach
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endforeach
                        </main>
                        <footer style="position: fixed; bottom: 0px; left: 0px; right: 0px; height: 50px;">{{ ucfirst($company->name) }} Freight Job Sheet For {{ $job->job_number }}</footer>
                    </div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('extra-js')
<script>
    window.addEventListener("load", window.print());
</script>
@endsection
@endsection
