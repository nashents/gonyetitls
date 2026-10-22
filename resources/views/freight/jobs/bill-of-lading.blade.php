@extends('layouts.manifest')

@section('title')
    Bill of Lading | {{ $job->job_number }}
@endsection

@section('extra-css')
<style>
  :root { --accent: {{ $company->color ?? '#123bdc' }}; }
</style>
@endsection

@section('content')

    <div class="action-bar" data-screen-only>
      <button class="btn" onclick="window.history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>
      <button type="button" class="btn" id="btnPrint" data-action="print">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" />
        </svg>
        Print
      </button>
    </div>

    <section class="manifest" data-entity="bill of lading">

      {{-- ===== Header ===== --}}
      <header class="manifest__header">
        <div class="brand">
          <img class="brand__logo"
               src="{{ asset('images/uploads/'.$company->logo) }}"
               alt="Company Logo"
               onerror="this.style.display='none'" />
          <div>
            <div class="brand__name">{{ $company->name }}</div>
            <div class="brand__meta">
              {{ $company->street_address }} {{ $company->suburb }}{{ $company->city ? ', '.$company->city : '' }} {{ $company->country }}<br />
              Tel: {{ $company->phonenumber }}
            </div>
          </div>
        </div>

        <aside class="meta">
          <div style="float:right; margin-left:8px;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode('BL: '.($document->document_number ?? $job->job_number)) }}" alt="QR Code" width="90" height="90" />
          </div>
          <h1>Bill of Lading</h1>
          <dl class="meta-grid">
            <dt>B/L Number</dt>
            <dd class="mono">{{ $document->document_number ?? 'DRAFT — NOT YET ISSUED' }}</dd>
            <dt>Job #</dt>
            <dd class="mono">{{ $job->job_number }}</dd>
            <dt>Shipment #</dt>
            <dd class="mono">{{ $shipment->shipment_number ?? '—' }}</dd>
            <dt>Date of Issue</dt>
            <dd>{{ $document && $document->issue_date ? $document->issue_date->format('d M Y') : '—' }}</dd>
          </dl>
        </aside>
      </header>

      <div class="hr"></div>

      @if (!$shipment)
        <p class="text-center">This job has no shipment to draft a Bill of Lading from yet.</p>
      @else

      {{-- ===== Shipper & Consignee ===== --}}
      <section class="grid-2">
        <div class="card">
          <h3>Shipper / Exporter</h3>
          <div class="kv">
            <div class="k">Name</div>
            <div class="v">{{ $job->customer?->name }}</div>
            <div class="k">Address</div>
            <div class="v">{{ $job->customer?->street_address }} {{ $job->customer?->city }} {{ $job->customer?->country }}</div>
            <div class="k">Contact</div>
            <div class="v">{{ $job->customer?->phonenumber }}{{ $job->customer?->email ? ' / '.$job->customer->email : '' }}</div>
          </div>
        </div>
        <div class="card">
          <h3>Consignee</h3>
          @php $consigneeParty = $shipment->parties->firstWhere('party_type', 'consignee'); @endphp
          @if ($consigneeParty)
            @php $consignee = $consigneeParty->party(); @endphp
            <div class="kv">
              <div class="k">Name</div>
              <div class="v">{{ $consignee?->name }}</div>
              @if ($consignee && ($consignee->street_address ?? null))
              <div class="k">Address</div>
              <div class="v">{{ $consignee->street_address }} {{ $consignee->city ?? '' }} {{ $consignee->country ?? '' }}</div>
              @endif
              @if ($consignee && ($consignee->phonenumber ?? $consignee->email ?? null))
              <div class="k">Contact</div>
              <div class="v">{{ $consignee->phonenumber ?? '' }}{{ ($consignee->email ?? null) ? ' / '.$consignee->email : '' }}</div>
              @endif
            </div>
          @else
            <div class="kv"><div class="k">Name</div><div class="v">—</div></div>
          @endif
        </div>
      </section>

      @php $otherParties = $shipment->parties->where('party_type', '!=', 'consignee'); @endphp
      @if ($otherParties->isNotEmpty())
      <div class="card" style="margin-top:12px">
        <h3>Other Parties</h3>
        <div class="kv">
          @foreach ($otherParties as $party)
            <div class="k">{{ $party->role ?: ucfirst(str_replace('_', ' ', $party->party_type)) }}</div>
            <div class="v">{{ $party->party()?->name }}</div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- ===== Carrier & Ports ===== --}}
      <section class="grid-2">
        <div class="card">
          <h3>Carrier / Vessel</h3>
          <div class="kv">
            <div class="k">Shipping Line</div>
            <div class="v">{{ $shipment->containers->first()?->shipping_line?->name ?: ($document->carrier_name ?? '—') }}</div>
            @if ($shipment->containers->isNotEmpty())
              @foreach ($shipment->containers as $container)
                <div class="k">Container / Seal</div>
                <div class="v">{{ $container->container_number }} ({{ $container->container_type }}){{ $container->seal_number ? ' / Seal: '.$container->seal_number : '' }}</div>
              @endforeach
            @endif
          </div>
        </div>
        <div class="card">
          <h3>Ports &amp; Terms</h3>
          <div class="kv">
            <div class="k">Port of Loading</div>
            <div class="v">{{ $job->fromDestination ? trim(($job->fromDestination->country->name ?? '').' '.$job->fromDestination->city) : '—' }}</div>
            <div class="k">Port of Discharge</div>
            <div class="v">{{ $job->toDestination ? trim(($job->toDestination->country->name ?? '').' '.$job->toDestination->city) : '—' }}</div>
            <div class="k">Freight Terms</div>
            <div class="v">{{ ucfirst($shipment->freight_terms ?? '') ?: '—' }}</div>
            <div class="k">Incoterm</div>
            <div class="v">{{ \App\Models\FreightJob::INCOTERMS[$shipment->incoterm] ?? ($shipment->incoterm ?: '—') }}</div>
          </div>
        </div>
      </section>

      {{-- ===== Cargo ===== --}}
      @if ($shipment->cargo_items->isNotEmpty())
      <section class="items">
        <h3>Marks &amp; Numbers / Description of Goods</h3>
        <table class="tbl" role="table" aria-label="Cargo Items">
          <thead>
            <tr>
              <th style="width:30px" class="center">#</th>
              <th style="width:120px">Marks &amp; Numbers</th>
              <th>Description</th>
              <th style="width:70px" class="right">Qty</th>
              <th style="width:60px">Unit</th>
              <th style="width:90px" class="right">Gross Wt (kg)</th>
            </tr>
          </thead>
          <tbody>
            @php $totalWeight = 0; @endphp
            @foreach ($shipment->cargo_items as $cargo)
              @php $totalWeight += (float) ($cargo->gross_weight ?? 0); @endphp
              <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $cargo->marks_and_numbers ?: '—' }}</td>
                <td>{{ $cargo->commodity }}{{ $cargo->hs_code ? ' (HS: '.$cargo->hs_code.')' : '' }}</td>
                <td class="right">{{ $cargo->quantity }}{{ $cargo->packages ? ' / '.$cargo->packages.' '.($cargo->package_type ?: 'pkgs') : '' }}</td>
                <td>{{ $cargo->uom }}</td>
                <td class="right">{{ $cargo->gross_weight ? number_format($cargo->gross_weight, 2) : '—' }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" class="right"><strong>Total Gross Weight</strong></td>
              <td class="right"><strong>{{ number_format($totalWeight, 2) }}</strong></td>
            </tr>
          </tfoot>
        </table>
      </section>
      @endif

      {{-- ===== Summary ===== --}}
      <div class="totals">
        <div class="notes">
          RECEIVED the goods in apparent good order and condition for carriage subject to the terms and conditions of this Bill of Lading.
          @if (!$document)
            <br><strong>No Bill of Lading has been issued/recorded for this shipment yet.</strong> Once the carrier issues the real document, record it under this job's Transport Documents tab to replace this draft with the official reference.
          @endif
        </div>
        <div class="card">
          <div class="kv">
            <div class="k">No. of Originals</div>
            <div class="v">{{ $document->number_of_originals ?? '—' }}</div>
            <div class="k">Place of Issue</div>
            <div class="v">{{ $document->place_of_issue ?? '—' }}</div>
            <div class="k">Freight Payable At</div>
            <div class="v">{{ $document->freight_payable_at ?? '—' }}</div>
          </div>
        </div>
      </div>

      {{-- ===== Sign-off ===== --}}
      <section class="signatures">
        <div class="sig">
          <div class="label">Shipper (Name &amp; Sign)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Master / Agent for Carrier (Name &amp; Sign)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Place &amp; Date</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
      </section>

      {{-- ===== Footer ===== --}}
      <footer class="manifest__footer">
        <div><strong>NB:</strong> THIS BILL OF LADING IS SUBJECT TO THE CARRIER'S STANDARD TERMS AND CONDITIONS OF CARRIAGE.</div>
      </footer>

      @endif

    </section>

@endsection
