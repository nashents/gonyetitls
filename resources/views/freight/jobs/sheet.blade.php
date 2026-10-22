@extends('layouts.manifest')

@section('title')
    Freight Job Sheet | {{ $job->job_number }}
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

    <section class="manifest" data-entity="job sheet">

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
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode('Freight Job: '.$job->job_number) }}" alt="QR Code" width="90" height="90" />
          </div>
          <h1>Freight Job Sheet</h1>
          <dl class="meta-grid">
            <dt>Job #</dt>
            <dd class="mono">{{ $job->job_number }}</dd>
            <dt>Customer Ref</dt>
            <dd>{{ $job->customer_reference ?: '—' }}</dd>
            <dt>Date</dt>
            <dd>{{ $job->opened_at ? $job->opened_at->format('d M Y') : $job->created_at->format('d M Y') }}</dd>
            <dt>Status</dt>
            <dd>{{ ucwords(str_replace('_', ' ', $job->status)) }}</dd>
            <dt>Created By</dt>
            <dd>{{ $job->user?->name }} {{ $job->user?->surname }}</dd>
          </dl>
        </aside>
      </header>

      <div class="hr"></div>

      {{-- ===== Customer & Job Details ===== --}}
      <section class="grid-2">
        <div class="card">
          <h3>Customer</h3>
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
          <h3>Job Details</h3>
          <div class="kv">
            <div class="k">Service Type</div>
            <div class="v">{{ $job->freight_service_type?->name ?: '—' }}</div>
            <div class="k">Mode</div>
            <div class="v">{{ ucfirst($job->primary_transport_mode ?? '') ?: '—' }}</div>
            <div class="k">Import / Export</div>
            <div class="v">{{ ucfirst(str_replace('_', ' ', $job->import_export_type ?? '')) ?: '—' }}</div>
            <div class="k">Incoterm</div>
            <div class="v">{{ \App\Models\FreightJob::INCOTERMS[$job->incoterm] ?? ($job->incoterm ?: '—') }}</div>
            <div class="k">Currency</div>
            <div class="v">{{ $job->currency?->name ?: '—' }}</div>
          </div>
        </div>
      </section>

      {{-- ===== Route & Team ===== --}}
      <section class="grid-2">
        <div class="card">
          <h3>Route</h3>
          <div class="kv">
            <div class="k">Origin</div>
            <div class="v">{{ $job->fromDestination ? trim(($job->fromDestination->country->name ?? '').' '.$job->fromDestination->city) : '—' }}</div>
            <div class="k">Destination</div>
            <div class="v">{{ $job->toDestination ? trim(($job->toDestination->country->name ?? '').' '.$job->toDestination->city) : '—' }}</div>
            @if ($job->quotation)
            <div class="k">Quotation</div>
            <div class="v">{{ $job->quotation->quotation_number }}</div>
            @endif
          </div>
        </div>
        <div class="card">
          <h3>Team</h3>
          <div class="kv">
            <div class="k">Salesperson</div>
            <div class="v">{{ $salesperson ? $salesperson->name.' '.$salesperson->surname : '—' }}</div>
            <div class="k">Operations Officer</div>
            <div class="v">{{ $operationsOfficer ? $operationsOfficer->name.' '.$operationsOfficer->surname : '—' }}</div>
            <div class="k">Clearing Officer</div>
            <div class="v">{{ $clearingOfficer ? $clearingOfficer->name.' '.$clearingOfficer->surname : '—' }}</div>
          </div>
        </div>
      </section>

      {{-- ===== Per-shipment detail ===== --}}
      @foreach ($job->shipments as $shipment)
        <div class="hr"></div>
        <h3 class="upper">Shipment {{ $shipment->shipment_number }}</h3>

        <section class="grid-2">
          <div class="card">
            <h3>Shipment Details</h3>
            <div class="kv">
              <div class="k">Mode</div>
              <div class="v">{{ ucfirst($shipment->mode ?? '') ?: '—' }}</div>
              <div class="k">Shipment Type</div>
              <div class="v">{{ $shipment->shipment_type ?: '—' }}</div>
              <div class="k">Loading Point</div>
              <div class="v">{{ $shipment->loading_point?->name ?: '—' }}</div>
              <div class="k">Offloading Point</div>
              <div class="v">{{ $shipment->offloading_point?->name ?: '—' }}</div>
              <div class="k">ETD</div>
              <div class="v">{{ $shipment->etd ? $shipment->etd->format('d M Y') : '—' }}</div>
              <div class="k">ETA</div>
              <div class="v">{{ $shipment->eta ? $shipment->eta->format('d M Y') : '—' }}</div>
              <div class="k">Booking Ref</div>
              <div class="v">{{ $shipment->booking_reference ?: '—' }}</div>
              <div class="k">Freight Terms</div>
              <div class="v">{{ ucfirst($shipment->freight_terms ?? '') ?: '—' }}</div>
            </div>
          </div>
          @if ($shipment->containers->isNotEmpty())
          <div class="card">
            <h3>Container(s)</h3>
            <div class="kv">
              @foreach ($shipment->containers as $container)
                <div class="k">{{ $container->container_number }}</div>
                <div class="v">
                  {{ $container->container_type }} — {{ \App\Models\ShippingContainer::LIFECYCLE_STAGES[$container->status] ?? $container->status }}
                  @if ($container->shipping_line) / {{ $container->shipping_line->name }} @endif
                </div>
              @endforeach
            </div>
          </div>
          @endif
        </section>

        @if ($shipment->cargo_items->isNotEmpty())
        <section class="items">
          <h3>Cargo / Items</h3>
          <table class="tbl" role="table" aria-label="Cargo Items">
            <thead>
              <tr>
                <th style="width:30px" class="center">#</th>
                <th>Description</th>
                <th style="width:90px">HS Code</th>
                <th style="width:70px" class="right">Qty</th>
                <th style="width:60px">Unit</th>
                <th style="width:90px" class="right">Gross Wt (kg)</th>
              </tr>
            </thead>
            <tbody>
              @php $shipmentWeight = 0; @endphp
              @foreach ($shipment->cargo_items as $cargo)
                @php $shipmentWeight += (float) ($cargo->gross_weight ?? 0); @endphp
                <tr>
                  <td class="center">{{ $loop->iteration }}</td>
                  <td>{{ $cargo->commodity }}</td>
                  <td>{{ $cargo->hs_code ?: '—' }}</td>
                  <td class="right">{{ $cargo->quantity }}</td>
                  <td>{{ $cargo->uom }}</td>
                  <td class="right">{{ $cargo->gross_weight ? number_format($cargo->gross_weight, 2) : '—' }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="5" class="right"><strong>Total Gross Weight</strong></td>
                <td class="right"><strong>{{ number_format($shipmentWeight, 2) }}</strong></td>
              </tr>
            </tfoot>
          </table>
        </section>
        @endif

        @if ($shipment->parties->isNotEmpty())
        <div class="card" style="margin-top:12px">
          <h3>Parties</h3>
          <div class="kv">
            @foreach ($shipment->parties as $party)
              <div class="k">{{ $party->role ?: ucfirst(str_replace('_', ' ', $party->party_type)) }}</div>
              <div class="v">{{ $party->party()?->name }}</div>
            @endforeach
          </div>
        </div>
        @endif
      @endforeach

      @if ($job->notes)
      <div class="totals">
        <div class="notes">{{ $job->notes }}</div>
      </div>
      @endif

      {{-- ===== Sign-off ===== --}}
      <section class="signatures">
        <div class="sig">
          <div class="label">Prepared By (Name &amp; Sign)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Checked By (Name &amp; Sign)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Approved By (Name &amp; Sign)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
      </section>

      {{-- ===== Footer ===== --}}
      <footer class="manifest__footer">
        <div><strong>NB:</strong> THIS IS AN INTERNAL OPERATIONAL RECORD AND HAS NO LEGAL OR CUSTOMS STANDING.</div>
      </footer>

    </section>

@endsection
