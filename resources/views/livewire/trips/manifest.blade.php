<div>

@section('extra-css')
<style>
  :root {
    --accent: {{$company->color}};
  }

  @page {
    size: A4 portrait;
    margin: 8mm;
  }

  @media print {
    body, section, .card, .kv, .tbl, .manifest { font-size: 8px !important; }
    h1 { font-size: 13px !important; }
    h3 { font-size: 9px !important; margin: 1px 0 !important; }
    .manifest__header { margin-bottom: 2px !important; }
    .brand__logo { max-height: 36px !important; }
    .brand__name { font-size: 11px !important; }
    .brand__meta { font-size: 7px !important; }
    .meta-grid { font-size: 8px !important; gap: 1px 6px !important; }
    section, header, footer { margin: 0 !important; padding: 0 !important; }
    .grid-2 { gap: 3px !important; margin: 2px 0 !important; }
    .card { margin: 0 !important; padding: 2px 4px !important; }
    .kv { row-gap: 1px !important; }
    .k, .v { font-size: 8px !important; padding: 0 !important; }
    h1, h3 { margin: 2px 0 !important; padding: 0 !important; }
    .hr { margin: 1px 0 !important; }
    .items { margin: 2px 0 !important; padding: 0 !important; }
    .tbl th, .tbl td { font-size: 7.5px !important; padding: 1px 3px !important; }
    .totals { margin: 2px 0 0 !important; padding: 0 !important; gap: 3px !important; }
    .notes { font-size: 7px !important; }
    .signatures { margin-top: 3px !important; display: grid; grid-template-columns: repeat(5, 1fr); gap: 3px !important; }
    .sig { margin: 0 !important; padding: 2px 3px !important; min-height: 28px !important; }
    .sig .label { font-size: 7px !important; }
    .sig .line { margin-top: 6px !important; }
    .manifest__footer { margin-top: 2px !important; padding: 0 !important; font-size: 7px !important; }
    .action-bar { display: none !important; }
    [data-screen-only] { display: none !important; }
  }
</style>
@endsection

    <div class="action-bar">
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

    <section class="manifest" data-entity="manifest">

      {{-- ===== Header ===== --}}
      <header class="manifest__header">
        <div class="brand">
          <img class="brand__logo"
               src="{{ asset('images/uploads/'.$company->logo) }}"
               alt="Company Logo"
               onerror="this.style.display='none'" />
          <div>
            <div class="brand__name" data-field="company.name">{{ $company->name }}</div>
            <div class="brand__meta">
              <span data-field="company.address">
                {{ $company->street_address }} {{ $company->suburb }}
                {{ $company->city ? ', '.$company->city : '' }}
                {{ $company->country }}
              </span><br />
              Tel: <span data-field="company.phone">{{ $company->phonenumber }}</span>
              · Email: <span data-field="company.email">{{ $company->email }}</span>
            </div>
          </div>
        </div>

        <aside class="meta">
          <h1>Transport Manifest</h1>
          <dl class="meta-grid">
            <dt>Manifest #</dt>
            <dd class="mono" data-field="manifest.number">{{ $trip->manifest_number }}</dd>
            <dt>Trip #</dt>
            <dd class="mono" data-field="trip.number">
              {{ $trip->trip_number }}{{ $trip->trip_ref ? ' / '.$trip->trip_ref : '' }}
            </dd>
            <dt>Date</dt>
            <dd data-field="manifest.date">{{ $trip->start_date }}</dd>
            <dt>Created By</dt>
            <dd data-field="created_by">{{ $trip->user?->name }} {{ $trip->user?->surname }}</dd>
          </dl>
        </aside>
      </header>

      <div class="hr"></div>

      {{-- ===== Transporter & Route ===== --}}
      <section class="grid-2">

        <div class="card">
          <h3>Transporter &amp; Vehicle</h3>
          <div class="kv">
            <div class="k">Transporter</div>
            <div class="v">{{ $trip->transporter?->name }}</div>

            <div class="k">Horse | Trailer(s)</div>
            <div class="v">
              @if ($trip->horse)
                {{ $trip->horse->registration_number }}
              @elseif ($trip->vehicle)
                {{ $trip->vehicle->registration_number }}
              @endif
              @if ($trip->trailers->count())
                |
                @foreach ($trip->trailers as $trailer)
                  {{ $trailer->registration_number }}@if (!$loop->last), @endif
                @endforeach
              @endif
            </div>

            @if ($trip->driver)
              <div class="k">Driver / Passport #</div>
              <div class="v">
                {{ $trip->driver->employee?->name }}
                {{ $trip->driver->employee?->surname }}
                {{ $trip->driver->passport_number ? ' / '.$trip->driver->passport_number : '' }}
              </div>
            @endif
          </div>
        </div>

        <div class="card">
          <h3>Route</h3>
          <div class="kv">
            <div class="k">Origin</div>
            <div class="v">
              @if (isset($from))
                {{ $from->country?->name }} {{ $from->city }}
              @endif
              {{ $trip->loading_point?->name }}
            </div>

            <div class="k">Destination</div>
            <div class="v">
              @if (isset($to))
                {{ $to->country?->name }} {{ $to->city }}
              @endif
              {{ $trip->offloading_point?->name }}
            </div>

            <div class="k">Border(s)</div>
            <div class="v">
              @foreach ($trip->borders as $border)
                {{ $border->name }}@if (!$loop->last), @endif
              @endforeach
            </div>

            <div class="k">Clearing Agent(s)</div>
            <div class="v">
              @foreach ($trip->clearing_agents as $agent)
                {{ $agent->name }}@if (!$loop->last), @endif
              @endforeach
            </div>
          </div>
        </div>

      </section>

      @php
        $ttos        = $trip->trip_transport_orders ?? collect();
        $multiOrder  = $ttos->count() > 1;
        $totalQty    = 0;
        $totalWeight = 0;
        $totalVolume = 0;
      @endphp

      @if ($ttos->isNotEmpty())

        {{-- ===== Consignor / Consignee — collapsed across all TTOs ===== --}}
        @php
          // Build unique shippers and consignees keyed by id to avoid duplicates
          $shippers   = collect();
          $consignees = collect();

          foreach ($ttos as $tto) {
            $to = $tto->transport_order;
            $shipper   = $to?->customer  ?? $trip->customer;
            $consignee = $to?->consignee ?? ($trip->consignee ?? null);

            if ($shipper   && !$shippers->has($shipper->id))   $shippers->put($shipper->id, $shipper);
            if ($consignee && !$consignees->has($consignee->id)) $consignees->put($consignee->id, $consignee);
          }
        @endphp

        <section class="grid-2">

          <div class="card">
            <h3>Consignor(s) (Shipper)</h3>
            <div class="kv">
              @foreach ($shippers as $i => $shipper)
                @if ($shippers->count() > 1)
                  <div class="k" style="grid-column:1/-1; font-weight:600;">{{ $loop->iteration }}.</div>
                @endif
                <div class="k">Name</div>
                <div class="v">{{ $shipper->name }}</div>
                <div class="k" data-screen-only>Ref</div>
                <div class="v" data-screen-only>{{ $shipper->custom_ref }}</div>
                <div class="k">Address</div>
                <div class="v">
                  {{ $shipper->street_address }}
                  {{ $shipper->suburb ? $shipper->suburb.', ' : '' }}
                  {{ $shipper->city }}
                  {{ $shipper->country }}
                </div>
                <div class="k">Contact</div>
                <div class="v">
                  {{ $shipper->phonenumber }}
                  {{ $shipper->email ? ' / '.$shipper->email : '' }}
                </div>
              @endforeach
            </div>
          </div>

          <div class="card">
            <h3>Consignee(s) (Receiver)</h3>
            <div class="kv">
              @foreach ($consignees as $consignee)
                @if ($consignees->count() > 1)
                  <div class="k" style="grid-column:1/-1; font-weight:600;">{{ $loop->iteration }}.</div>
                @endif
                <div class="k">Name</div>
                <div class="v">{{ $consignee->name }}</div>
                <div class="k">Address</div>
                <div class="v">
                  {{ $consignee->street_address }}
                  {{ $consignee->suburb ? $consignee->suburb.', ' : '' }}
                  {{ $consignee->city }}
                  {{ $consignee->country }}
                </div>
                <div class="k">Contact</div>
                <div class="v">
                  {{ $consignee->phonenumber }}
                  {{ $consignee->email ? ' / '.$consignee->email : '' }}
                </div>
              @endforeach
            </div>
          </div>

        </section>

        {{-- ===== Single unified cargo table across all TTOs ===== --}}
        <section class="items">
          <h3>Cargo / Items</h3>
          <table class="tbl" role="table" aria-label="Cargo Items">
            <thead>
              <tr>
                <th style="width:30px" class="center">#</th>
                <th>Description</th>
                <th style="width:110px">Invoice #(s)</th>
                <th style="width:80px" class="right">Qty</th>
                <th style="width:60px">Unit</th>
                <th style="width:90px" class="right">Weight (Tons)</th>
                <th style="width:90px" class="right">SKU / Ref</th>
                <th style="width:90px" class="right">BillOfEntry#</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($ttos as $tto)
                @php
                  $to         = $tto->transport_order;
                  $boe         = $tto->bill_of_entry;
                  $cargo      = $to?->cargo;
                  $cargoType  = $cargo?->type;
                  $ttoQty     = $to?->quantity        ?? $trip->quantity;
                  $ttoLit20   = $to?->litreage_at_20  ?? $trip->litreage_at_20;
                  $ttoWeight  = $to?->weight          ?? $trip->weight;
                  $ttoVolume  = $to?->volume          ?? $trip->volume;
                  $ttoMeasure = $to?->units_of_measure     ?? $trip->units_of_measure;
                  $ttoDetails = $to?->cargo_details   ?? $trip->cargo_details;
                  $displayQty = $cargoType === 'Liquid' ? $ttoLit20 : $ttoQty;

                  $totalQty    += (float) $displayQty;
                  $totalWeight += (float) $ttoWeight;
                  $totalVolume += (float) $ttoVolume;

                  $invoiceNumbers = ($tto->invoice_items ?? $trip->invoice_items ?? collect())
                      ->map(fn($item) => $item->invoice?->invoice_number)
                      ->filter()
                      ->unique()
                      ->values();
                @endphp
                <tr>
                  <td class="center">{{ $loop->iteration }}</td>
                  <td>{{ $cargo?->name }} {{ $ttoDetails }}</td>
                  <td class="mono">
                    @foreach ($invoiceNumbers as $num)
                      {{ $num }}@if (!$loop->last), @endif
                    @endforeach
                  </td>
                  <td class="right">{{ $displayQty }}</td>
                  <td>{{ $ttoMeasure?->name }}</td>
                  <td class="right">{{ $ttoWeight }}</td>
                  <td class="right">{{ $cargo?->sku }}</td>
                  <td class="right">{{ $boe }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="right"><strong>Totals</strong></td>
                <td class="right"><strong>{{ $totalQty }}</strong></td>
                <td></td>
                <td class="right"><strong>{{ $totalWeight }}</strong></td>
               
              </tr>
            </tfoot>
          </table>
        </section>

      @else

        {{-- ===== Fallback: no TTOs ===== --}}
        @php
          $shipper   = $trip->customer  ?? null;
          $consignee = $trip->consignee ?? null;
        @endphp

        <section class="grid-2">
          <div class="card">
            <h3>Consignor (Shipper)</h3>
            <div class="kv">
              <div class="k">Name</div>    <div class="v">{{ $shipper?->name }}</div>
              <div class="k">Address</div> <div class="v">{{ $shipper?->street_address }} {{ $shipper?->suburb ? $shipper->suburb.', ' : '' }}{{ $shipper?->city }} {{ $shipper?->country }}</div>
              <div class="k">Contact</div> <div class="v">{{ $shipper?->phonenumber }}{{ $shipper?->email ? ' / '.$shipper->email : '' }}</div>
            </div>
          </div>
          <div class="card">
            <h3>Consignee (Receiver)</h3>
            <div class="kv">
              <div class="k">Name</div>    <div class="v">{{ $consignee?->name }}</div>
              <div class="k">Address</div> <div class="v">{{ $consignee?->street_address }} {{ $consignee?->suburb ? $consignee->suburb.', ' : '' }}{{ $consignee?->city }} {{ $consignee?->country }}</div>
              <div class="k">Contact</div> <div class="v">{{ $consignee?->phonenumber }}{{ $consignee?->email ? ' / '.$consignee->email : '' }}</div>
            </div>
          </div>
        </section>

        <section class="items">
          <h3>Cargo / Items</h3>
          <table class="tbl" role="table" aria-label="Cargo Items">
            <thead>
              <tr>
                <th style="width:30px" class="center">#</th>
                <th>Description</th>
                <th style="width:110px">Invoice #(s)</th>
                <th style="width:80px" class="right">Qty</th>
                <th style="width:60px">Unit</th>
                <th style="width:90px" class="right">Weight (Tons)</th>
                <th style="width:90px" class="right">SKU / Ref</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="center">1</td>
                <td>{{ $cargo->name }} {{ $trip->cargo_details }}</td>
                <td class="mono">
                  @php
                    $invoiceNumbers = ($trip->invoice_items ?? collect())
                        ->map(fn($item) => $item->invoice?->invoice_number)
                        ->filter()->unique()->values();
                  @endphp
                  @foreach ($invoiceNumbers as $num)
                    {{ $num }}@if (!$loop->last), @endif
                  @endforeach
                </td>
                <td class="right">
                  @if ($cargo->type === 'Solid') {{ $trip->quantity }}
                  @elseif ($cargo->type === 'Liquid') {{ $trip->litreage_at_20 }}
                  @endif
                </td>
                <td>{{ $trip->measurement }}</td>
                <td class="right">{{ $trip->weight }}</td>
                <td class="right">{{ $cargo->sku }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="right"><strong>Totals</strong></td>
                <td class="right">
                  @if ($cargo->type === 'Solid') {{ $trip->quantity }}
                  @elseif ($cargo->type === 'Liquid') {{ $trip->litreage_at_20 }}
                  @endif
                </td>
                <td></td>
                <td class="right">{{ $trip->weight }}</td>
                <td class="right">{{ $trip->volume }}</td>
              </tr>
            </tfoot>
          </table>
        </section>

      @endif

      {{-- ===== Summary / Seal ===== --}}
      <div class="totals">
        <div class="notes">
          I HEREBY CERTIFY THAT THE GOODS ON THIS MANIFEST ARE A TRUE REFLECTION
          OF ALL GOODS CARRIED ON THE ABOVE-MENTIONED VEHICLES
        </div>
        <div class="card">
          <div class="kv">
            <div class="k">Gross / Net Weight</div>
            <div class="v">
              {{ $trip->weight ? $trip->weight.'t' : '' }}
              {{ $trip->net_weight ? ' / '.$trip->net_weight.'t' : '' }}
            </div>
            <div class="k">Seal #</div>
            <div class="v">{{ $trip->seal_number }}</div>
            <div class="k">Container #</div>
            <div class="v">{{ $trip->container_number }}</div>
          </div>
        </div>
      </div>

      {{-- ===== Sign-off ===== --}}
      <section class="signatures">
        <div class="sig label-only"><div class="label">Custom Stamp — Entry</div></div>
        <div class="sig label-only"><div class="label">Custom Stamp — Exit</div></div>
        <div class="sig label-only"><div class="label">Report No(s)</div></div>
        <div class="sig">
          <div class="label">Driver (Name &amp; Sign)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Receiver (Name, Sign &amp; Date)</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
      </section>

      {{-- ===== Footer ===== --}}
      <footer class="manifest__footer">
        <div><strong>NB:</strong> ALL GOODS ARE CARRIED AT OWNER'S RISK</div>
      </footer>

    </section>

</div>