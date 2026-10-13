@extends('layouts.previews')
@section('title')
    Bill of Lading | {{ $job->job_number }}
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
                            <div style="padding-top: 25px; padding-bottom:5px">
                                <center><h2>BILL OF LADING</h2></center>
                                <center>
                                    @if ($document)
                                        <strong>{{ $document->document_number }}</strong>
                                    @else
                                        <span style="color:#c0392b">DRAFT — NOT YET ISSUED</span>
                                    @endif
                                </center>
                            </div>
                        </header>
                        <main>
                            @if (!$shipment)
                                <p class="text-center">This job has no shipment to draft a Bill of Lading from yet.</p>
                            @else
                            <table>
                                <tbody>
                                    <tr>
                                        <th class="text-center"><strong>Job Number</strong></th>
                                        <td class="text-center">{{ $job->job_number }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Shipment Number</strong></th>
                                        <td class="text-center">{{ $shipment->shipment_number }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Shipper / Exporter</strong></th>
                                        <td class="text-center">
                                            {{ $job->customer?->name }}<br>
                                            {{ $job->customer?->street_address }} {{ $job->customer?->city }} {{ $job->customer?->country }}
                                        </td>
                                    </tr>
                                    @forelse ($shipment->parties as $party)
                                    <tr>
                                        <th class="text-center"><strong>{{ $party->role ?: ucfirst(str_replace('_', ' ', $party->party_type)) }}</strong></th>
                                        <td class="text-center">
                                            {{ $party->party()?->name }}
                                            @php $resolved = $party->party(); @endphp
                                            @if ($resolved && ($resolved->street_address ?? null))
                                                <br>{{ $resolved->street_address }} {{ $resolved->city ?? '' }} {{ $resolved->country ?? '' }}
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <th class="text-center"><strong>Consignee</strong></th>
                                        <td class="text-center">—</td>
                                    </tr>
                                    @endforelse
                                    <tr>
                                        <th class="text-center"><strong>Carrier / Shipping Line</strong></th>
                                        <td class="text-center">{{ $shipment->containers->first()?->shipping_line?->name ?: ($document->carrier_name ?? '—') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Port of Loading</strong></th>
                                        <td class="text-center">{{ $job->fromDestination ? trim(($job->fromDestination->country->name ?? '').' '.$job->fromDestination->city) : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Port of Discharge</strong></th>
                                        <td class="text-center">{{ $job->toDestination ? trim(($job->toDestination->country->name ?? '').' '.$job->toDestination->city) : '—' }}</td>
                                    </tr>
                                    @if ($shipment->containers->isNotEmpty())
                                    <tr>
                                        <th class="text-center"><strong>Container / Seal No.</strong></th>
                                        <td class="text-center">
                                            @foreach ($shipment->containers as $container)
                                                {{ $container->container_number }} ({{ $container->container_type }}) @if ($container->seal_number) / Seal: {{ $container->seal_number }} @endif<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif
                                    @if ($shipment->cargo_items->isNotEmpty())
                                    <tr>
                                        <th class="text-center"><strong>Marks &amp; Numbers / Description of Goods</strong></th>
                                        <td class="text-center">
                                            @foreach ($shipment->cargo_items as $cargo)
                                                {{ $cargo->marks_and_numbers ?: '—' }} — {{ $cargo->commodity }}
                                                @if ($cargo->hs_code) (HS: {{ $cargo->hs_code }}) @endif<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Quantity &amp; Packages</strong></th>
                                        <td class="text-center">
                                            @foreach ($shipment->cargo_items as $cargo)
                                                {{ $cargo->quantity }} {{ $cargo->uom }}
                                                @if ($cargo->packages) ({{ $cargo->packages }} {{ $cargo->package_type ?: 'packages' }}) @endif<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Gross Weight</strong></th>
                                        <td class="text-center">
                                            @foreach ($shipment->cargo_items as $cargo)
                                                {{ $cargo->gross_weight ? number_format($cargo->gross_weight, 2).' kg' : '—' }}<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="text-center"><strong>Freight Terms</strong></th>
                                        <td class="text-center">{{ ucfirst($shipment->freight_terms ?? '') ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Incoterm</strong></th>
                                        <td class="text-center">{{ \App\Models\FreightJob::INCOTERMS[$shipment->incoterm] ?? ($shipment->incoterm ?: '—') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>No. of Original B/Ls</strong></th>
                                        <td class="text-center">{{ $document->number_of_originals ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Place of Issue</strong></th>
                                        <td class="text-center">{{ $document->place_of_issue ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Date of Issue</strong></th>
                                        <td class="text-center">{{ $document && $document->issue_date ? $document->issue_date->format('d M Y') : '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @if (!$document)
                                <p class="text-center" style="padding-top:15px;color:#c0392b">
                                    No Bill of Lading has been issued/recorded for this shipment yet. Once the carrier issues the real document,
                                    record it under this job's Transport Documents tab to replace this draft with the official reference.
                                </p>
                            @endif
                            @endif
                        </main>
                        <footer style="position: fixed; bottom: 0px; left: 0px; right: 0px; height: 50px;">{{ ucfirst($company->name) }} Bill of Lading For {{ $job->job_number }}</footer>
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
