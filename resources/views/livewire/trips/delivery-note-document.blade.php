<div>

@section('extra-css')
<style>
  :root {
    --accent: {{ $company?->color }};
  }

  @page {
    size: A4 portrait;
    margin: 8mm;
  }

  .signatures.signatures--3 { grid-template-columns: repeat(3, 1fr); }
  .sig.esign { justify-content: space-between; min-height: 150px; }
  .sig.esign .sig-img { height: 70px; max-width: 100%; object-fit: contain; display: block; }
  .sig.esign .sig-who { font-size: 12px; font-weight: 600; }
  .sig.esign .sig-when { font-size: 11px; color: var(--muted); }
  .sig.esign .blank-row { display: flex; gap: 6px; align-items: flex-end; font-size: 11px; color: var(--muted); margin-top: 14px; }
  .sig.esign .blank-row span { white-space: nowrap; }
  .sig.esign .blank-row .line { flex: 1; margin-top: 0; }
  .sig-actions { display: flex; gap: 6px; margin-top: 8px; }
  .sig-actions .btn { font-size: 12px; padding: 4px 10px; }
  .badge-signed { display:inline-block; font-size: 10px; padding: 1px 6px; border-radius: 999px; background: #dcfce7; color: #166534; }

  .share-box { display:flex; gap:8px; align-items:center; margin-bottom: 12px; font-size: 12px; }
  .share-box input { flex: 1; padding: 6px 8px; border: 1px solid var(--line); border-radius: 8px; font-size: 12px; }

  .pad-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 16px; }
  .pad-dialog { background: #fff; border-radius: 12px; width: 100%; max-width: 560px; padding: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
  .pad-dialog h3 { margin: 0 0 10px 0; font-size: 16px; }
  .pad-dialog label { font-size: 12px; color: var(--muted); display:block; margin-top: 8px; }
  .pad-dialog input[type=text] { width: 100%; box-sizing: border-box; padding: 7px 9px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; }
  .pad-canvas-wrap { margin-top: 10px; border: 1px dashed #94a3b8; border-radius: 10px; background: #f8fafc; }
  .pad-canvas-wrap canvas { width: 100%; height: 200px; display: block; touch-action: none; cursor: crosshair; }
  .pad-error { color: #b91c1c; font-size: 12px; margin-top: 4px; }
  .pad-footer { display:flex; justify-content: space-between; gap: 8px; margin-top: 12px; }

  @media (max-width: 800px) {
    .signatures.signatures--3 { grid-template-columns: 1fr; }
  }

  @media print {
    body, section, .card, .kv, .tbl, .manifest { font-size: 8px !important; }
    h1 { font-size: 13px !important; }
    h3 { font-size: 9px !important; margin: 1px 0 !important; }
    .manifest__header { margin-bottom: 2px !important; }
    .brand__logo { width: 130px !important; height: 130px !important; max-height: 130px !important; }
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
    .signatures.signatures--3 { margin-top: 3px !important; grid-template-columns: repeat(3, 1fr) !important; gap: 3px !important; }
    .sig.esign { min-height: 90px !important; padding: 3px 4px !important; }
    .sig.esign .sig-img { height: 45px !important; }
    .sig.esign .sig-who, .sig.esign .sig-when, .sig.esign .blank-row { font-size: 7.5px !important; }
    .sig .label { font-size: 7.5px !important; }
    .manifest__footer { margin-top: 2px !important; padding: 0 !important; font-size: 7px !important; }
    .action-bar, .share-box, .sig-actions, .pad-overlay { display: none !important; }
    [data-screen-only] { display: none !important; }
  }
</style>
@endsection

    <div class="action-bar">
      @auth
        <button class="btn" onclick="window.history.back()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </button>
      @endauth
      @if ($canManage)
        <button type="button" class="btn" wire:click="generateShareLink">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
          Signing Link
        </button>
      @endif
      <button type="button" class="btn" id="btnPrint" data-action="print">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" />
        </svg>
        Print / Download
      </button>
    </div>

    @if ($shareLink)
      <div class="share-box manifest" style="margin-top:0; padding:10px 14px;">
        <span>Send this link to the people who must sign (valid 14 days, no login needed):</span>
        <input type="text" readonly value="{{ $shareLink }}" onclick="this.select()" id="dnShareLink" />
        <button type="button" class="btn" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('dnShareLink').value); this.textContent='Copied';">Copy</button>
      </div>
    @endif

    @php
      $deliveryNoteNumber = 'DN-'.$trip->trip_number;
      $ttos = $trip->trip_transport_orders ?? collect();
      $orderNumbers = $ttos->map(fn ($tto) => $tto->transport_order?->transport_order_number)->filter()->unique()->values();

      // One line per transport order on the trip (or the trip itself when it has none),
      // loaded figures from the delivery note record, falling back to the order/trip.
      $lines = collect();
      $sources = $ttos->isNotEmpty()
          ? $ttos->map(fn ($tto) => [$tto->transport_order, $tto->delivery_note])
          : collect([[null, $trip->delivery_note]]);

      foreach ($sources as [$order, $dn]) {
          $cargo = $order?->cargo ?? $trip->cargo;
          $liquid = $cargo?->type === 'Liquid';
          $loadedQty = $dn
              ? ($liquid ? ($dn->loaded_litreage_at_20 ?: $dn->loaded_litreage) : $dn->loaded_quantity)
              : ($liquid ? ($order?->litreage_at_20 ?? $trip->litreage_at_20) : ($order?->quantity ?? $trip->quantity));
          $deliveredQty = $dn ? ($liquid ? ($dn->offloaded_litreage_at_20 ?: $dn->offloaded_litreage) : $dn->offloaded_quantity) : null;
          $loadedWeight = $dn ? $dn->loaded_weight : ($order?->weight ?? $trip->weight);
          $deliveredWeight = $dn?->offloaded_weight;

          $lines->push([
              'description' => trim(($cargo?->name ?? '').' '.($order?->cargo_details ?? $trip->cargo_details)),
              'order' => $order?->transport_order_number,
              'unit' => $dn?->units_of_measure?->name ?? $order?->units_of_measure?->name ?? $trip->measurement,
              'loaded_qty' => (float) $loadedQty ?: null,
              'delivered_qty' => (float) $deliveredQty ?: null,
              'loaded_weight' => (float) $loadedWeight ?: null,
              'delivered_weight' => (float) $deliveredWeight ?: null,
              'loaded_date' => $dn?->loaded_date,
              'delivered_date' => $dn?->offloaded_date,
              'comments' => $dn?->comments,
          ]);
      }

      $variance = fn ($loaded, $delivered) => ($loaded !== null && $delivered !== null) ? round($loaded - $delivered, 3) : null;
      $formatDate = fn ($d) => $d ? rescue(fn () => \Carbon\Carbon::parse($d)->format('d M Y H:i'), $d, false) : null;
      $loadedDate = $formatDate($lines->pluck('loaded_date')->filter()->first() ?? $trip->start_date);
      $deliveredDate = $formatDate($lines->pluck('delivered_date')->filter()->first());
      $remarks = $lines->pluck('comments')->filter()->unique()->implode(' / ');

      $shippers = collect();
      $consignees = collect();
      foreach ($ttos as $tto) {
          $shipper = $tto->transport_order?->consignor ?? $trip->consignor;
          $consignee = $tto->transport_order?->consignee ?? $trip->consignee;
          if ($shipper) $shippers->put($shipper->id, $shipper);
          if ($consignee) $consignees->put($consignee->id, $consignee);
      }
      if ($ttos->isEmpty()) {
          if ($trip->consignor) $shippers->put($trip->consignor->id, $trip->consignor);
          if ($trip->consignee) $consignees->put($trip->consignee->id, $trip->consignee);
      }

      $qrData = collect([
          'Delivery Note'   => $deliveryNoteNumber,
          'Trip'            => $trip->trip_number.($trip->trip_ref ? '/'.$trip->trip_ref : ''),
          'Transport Order' => $orderNumbers->implode(', '),
      ])->filter()->map(fn ($value, $label) => $label.': '.$value)->implode("\n");
    @endphp

    <section class="manifest" data-entity="delivery note {{ $trip->trip_number }}">

      {{-- ===== Header ===== --}}
      <header class="manifest__header">
        <div class="brand">
          <img class="brand__logo"
               src="{{ asset('images/uploads/'.$company?->logo) }}"
               alt="Company Logo"
               onerror="this.style.display='none'" />
          <div>
            <div class="brand__name">{{ $company?->name }}</div>
            <div class="brand__meta">
              {{ $company?->street_address }} {{ $company?->suburb }}
              {{ $company?->city ? ', '.$company->city : '' }}
              {{ $company?->country }}<br />
              Tel: {{ $company?->phonenumber }}
            </div>
          </div>
        </div>

        <aside class="meta">
          <div style="float:right; margin-left:8px;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode($qrData) }}" alt="QR Code" width="90" height="90" />
          </div>
          <h1>Delivery Note</h1>
          <dl class="meta-grid">
            <dt>Delivery Note #</dt>
            <dd class="mono">{{ $deliveryNoteNumber }}</dd>
            <dt>Trip #</dt>
            <dd class="mono">{{ $trip->trip_number }}{{ $trip->trip_ref ? ' / '.$trip->trip_ref : '' }}</dd>
            @if ($orderNumbers->isNotEmpty())
              <dt>Transport Order #</dt>
              <dd class="mono">{{ $orderNumbers->implode(', ') }}</dd>
            @endif
            @if ($trip->manifest_number)
              <dt>Manifest #</dt>
              <dd class="mono">{{ $trip->manifest_number }}</dd>
            @endif
            <dt>Loading Date</dt>
            <dd>{{ $loadedDate }}</dd>
            <dt>Delivery Date</dt>
            <dd>{{ $deliveredDate }}</dd>
          </dl>
        </aside>
      </header>

      <div class="hr"></div>

      {{-- ===== Vehicle & Route ===== --}}
      <section class="grid-2">
        <div class="card">
          <h3>Vehicle</h3>
          <div class="kv">
            <div class="k">Horse | Trailer(s)</div>
            <div class="v">
              {{ $trip->horse?->registration_number ?? $trip->vehicle?->registration_number }}
              @if ($trip->trailers->count())
                | {{ $trip->trailers->pluck('registration_number')->implode(', ') }}
              @endif
            </div>
            @if ($trip->driver)
              <div class="k">Driver</div>
              <div class="v">
                {{ $trip->driver->employee?->name }} {{ $trip->driver->employee?->surname }}
                {{ $trip->driver->employee?->phonenumber ? ' / '.$trip->driver->employee->phonenumber : '' }}
              </div>
            @endif
            @if ($trip->transporter)
              <div class="k">Transporter</div>
              <div class="v">{{ $trip->transporter->name }}</div>
            @endif
          </div>
        </div>

        <div class="card">
          <h3>Route</h3>
          <div class="kv">
            <div class="k">Collected From</div>
            <div class="v">
              {{ $trip->loading_point?->name }}
              @if ($from) ({{ $from->city }}{{ $from->country?->name ? ', '.$from->country->name : '' }}) @endif
            </div>
            <div class="k">Delivered To</div>
            <div class="v">
              {{ $trip->offloading_point?->name }}
              @if ($to) ({{ $to->city }}{{ $to->country?->name ? ', '.$to->country->name : '' }}) @endif
            </div>
            <div class="k">Seal # / Container #</div>
            <div class="v">{{ $trip->seal_number }}{{ $trip->container_number ? ' / '.$trip->container_number : '' }}</div>
          </div>
        </div>
      </section>

      {{-- ===== Consignor / Consignee ===== --}}
      <section class="grid-2">
        @foreach (['Consignor (Shipper)' => $shippers, 'Consignee (Receiver)' => $consignees] as $title => $parties)
          <div class="card">
            <h3>{{ $title }}</h3>
            <div class="kv">
              @forelse ($parties as $party)
                @if ($parties->count() > 1)
                  <div class="k" style="grid-column:1/-1; font-weight:600;">{{ $loop->iteration }}.</div>
                @endif
                <div class="k">Name</div>
                <div class="v">{{ $party->name }}</div>
                <div class="k">Address</div>
                <div class="v">{{ $party->street_address }} {{ $party->suburb ? $party->suburb.', ' : '' }}{{ $party->city }} {{ $party->country }}</div>
                <div class="k">Contact</div>
                <div class="v">{{ $party->phonenumber }}{{ $party->email ? ' / '.$party->email : '' }}</div>
              @empty
                <div class="k">Name</div><div class="v"></div>
              @endforelse
            </div>
          </div>
        @endforeach
      </section>

      {{-- ===== Goods delivered ===== --}}
      <section class="items">
        <h3>Goods Delivered</h3>
        <table class="tbl" role="table" aria-label="Goods Delivered">
          <thead>
            <tr>
              <th style="width:30px" class="center">#</th>
              <th>Description</th>
              @if ($orderNumbers->count() > 1)
                <th style="width:100px">Transport Order</th>
              @endif
              <th style="width:60px">Unit</th>
              <th style="width:80px" class="right">Qty Loaded</th>
              <th style="width:80px" class="right">Qty Delivered</th>
              <th style="width:85px" class="right">Weight Loaded (t)</th>
              <th style="width:85px" class="right">Weight Delivered (t)</th>
              <th style="width:80px" class="right">Short / (Over)</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($lines as $line)
              @php
                $short = $variance($line['loaded_qty'], $line['delivered_qty']) ?? $variance($line['loaded_weight'], $line['delivered_weight']);
              @endphp
              <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $line['description'] }}</td>
                @if ($orderNumbers->count() > 1)
                  <td class="mono">{{ $line['order'] }}</td>
                @endif
                <td>{{ $line['unit'] }}</td>
                <td class="right">{{ $line['loaded_qty'] }}</td>
                <td class="right">{{ $line['delivered_qty'] }}</td>
                <td class="right">{{ $line['loaded_weight'] }}</td>
                <td class="right">{{ $line['delivered_weight'] }}</td>
                <td class="right">{{ $short === null ? '' : ($short < 0 ? '('.abs($short).')' : $short) }}</td>
              </tr>
            @endforeach
          </tbody>
          @if ($lines->count() > 1)
            <tfoot>
              <tr>
                <td colspan="{{ $orderNumbers->count() > 1 ? 4 : 3 }}" class="right"><strong>Totals</strong></td>
                <td class="right">{{ $lines->sum('loaded_qty') ?: '' }}</td>
                <td class="right">{{ $lines->sum('delivered_qty') ?: '' }}</td>
                <td class="right">{{ $lines->sum('loaded_weight') ?: '' }}</td>
                <td class="right">{{ $lines->sum('delivered_weight') ?: '' }}</td>
                <td></td>
              </tr>
            </tfoot>
          @endif
        </table>
      </section>

      {{-- ===== Remarks ===== --}}
      <div class="totals">
        <div class="notes">
          <strong>Remarks / Exceptions:</strong> {{ $remarks }}
        </div>
        <div class="notes">
          RECEIVED THE ABOVE GOODS IN GOOD ORDER AND CONDITION UNLESS OTHERWISE
          STATED UNDER REMARKS / EXCEPTIONS.
        </div>
      </div>

      {{-- ===== Sign-off: e-sign on screen, or sign the printed copy by hand ===== --}}
      <section class="signatures signatures--3">
        @foreach ($roles as $role => $label)
          @php $signed = $signatures->get($role); @endphp
          <div class="sig esign">
            <div class="label">
              {{ $label }}
              @if ($signed) <span class="badge-signed" data-screen-only>e-signed</span> @endif
            </div>

            @if ($signed)
              <img class="sig-img" src="{{ $signed->signature }}" alt="Signature of {{ $signed->signer_name }}" />
              <div>
                <div class="sig-who">{{ $signed->signer_name }}{{ $signed->signer_designation ? ' - '.$signed->signer_designation : '' }}</div>
                <div class="sig-when">Signed {{ $signed->signed_at?->format('d M Y H:i') }}</div>
              </div>
            @else
              <div>
                <div class="blank-row"><span>Name</span><div class="line"></div></div>
                <div class="blank-row"><span>Signature</span><div class="line"></div></div>
                <div class="blank-row"><span>Date</span><div class="line"></div></div>
              </div>
            @endif

            <div class="sig-actions" data-screen-only>
              @if (! $signed && $canSign)
                <button type="button" class="btn" wire:click="openSign('{{ $role }}')">Sign</button>
              @endif
              @if ($signed && $canManage)
                <button type="button" class="btn" wire:click="openSign('{{ $role }}')">Re-sign</button>
                <button type="button" class="btn" wire:click="clearSignature('{{ $role }}')"
                        onclick="confirm('Remove this signature?') || event.stopImmediatePropagation()">Clear</button>
              @endif
            </div>
          </div>
        @endforeach
      </section>

      {{-- ===== Footer ===== --}}
      <footer class="manifest__footer">
        <div><strong>NB:</strong> ALL GOODS ARE CARRIED AT OWNER'S RISK</div>
        <div>{{ $deliveryNoteNumber }}</div>
      </footer>

    </section>

    {{-- ===== Signature pad (screen only) ===== --}}
    @if ($signRole)
      <div class="pad-overlay">
        <div class="pad-dialog">
          <h3>Sign as: {{ $roles[$signRole] ?? '' }}</h3>

          <label for="signer_name">Full name</label>
          <input type="text" id="signer_name" wire:model.defer="signer_name" placeholder="Name of person signing" />
          @error('signer_name') <div class="pad-error">{{ $message }}</div> @enderror

          <label for="signer_designation">Designation / Company (optional)</label>
          <input type="text" id="signer_designation" wire:model.defer="signer_designation" placeholder="e.g. Stores Clerk, ABC Ltd" />

          <label>Draw your signature below</label>
          <div class="pad-canvas-wrap" wire:ignore>
            <canvas id="dnSignaturePad"></canvas>
          </div>
          @error('signature') <div class="pad-error">{{ $message }}</div> @enderror
          <div class="pad-error" id="dnPadEmpty" style="display:none;">Please draw a signature first.</div>

          <div class="pad-footer">
            <button type="button" class="btn" onclick="window.dnSignaturePad && window.dnSignaturePad.clear()">Clear pad</button>
            <div style="display:flex; gap:8px;">
              <button type="button" class="btn" wire:click="cancelSign">Cancel</button>
              <button type="button" class="btn" onclick="window.dnSignaturePad && window.dnSignaturePad.save(this)">Save Signature</button>
            </div>
          </div>
        </div>
      </div>
    @endif

</div>
