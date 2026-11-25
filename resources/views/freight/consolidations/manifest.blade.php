@extends('layouts.manifest')

@section('title')
    LCL Manifest | {{ $header['consolidation_number'] }}
@endsection

@section('extra-css')
<style>
  :root { --accent: {{ $company->color ?? '#123bdc' }}; }
  @page { size: A4 landscape; margin: 10mm; }
  .tbl td, .tbl th { font-size: 11px; }
  .tbl tr.client-start td { border-top: 2px solid #999; }
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
      <a class="btn" href="{{ route('freight.consolidations.manifest.excel', $consolidation->id) }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
        </svg>
        Excel
      </a>
      <button type="button" class="btn" id="btnPrint" data-action="print">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" />
        </svg>
        Print
      </button>
    </div>

    <section class="manifest" data-entity="cargo manifest">

      <header class="manifest__header">
        <div class="brand">
          @if ($company)
            <img class="brand__logo" src="{{ asset('images/uploads/'.$company->logo) }}" alt="Company Logo" onerror="this.style.display='none'" />
            <div>
              <div class="brand__name">{{ $company->name }}</div>
              <div class="brand__meta">
                {{ $company->street_address }} {{ $company->suburb }}{{ $company->city ? ', '.$company->city : '' }} {{ $company->country }}<br />
                Tel: {{ $company->phonenumber }}
              </div>
            </div>
          @endif
        </div>

        <aside class="meta">
          <h1>LCL Cargo Manifest</h1>
          <dl class="meta-grid">
            <dt>Consolidation #</dt>
            <dd class="mono">{{ $header['consolidation_number'] }}</dd>
            <dt>Master Job</dt>
            <dd class="mono">{{ $header['master_job'] ?: '—' }}</dd>
            <dt>Master B/L</dt>
            <dd class="mono">{{ $header['master_bl'] ?: '—' }}</dd>
            <dt>Printed</dt>
            <dd>{{ now()->format('d M Y H:i') }}</dd>
          </dl>
        </aside>
      </header>

      <div class="hr"></div>

      <section class="grid-2">
        <div class="card">
          <h3>Voyage</h3>
          <div class="kv">
            <div class="k">Mode</div>
            <div class="v">{{ $header['mode'] ?: '—' }}</div>
            <div class="k">Carrier</div>
            <div class="v">{{ $header['carrier'] ?: '—' }}{{ $header['voyage'] ? ' / '.$header['voyage'] : '' }}</div>
            <div class="k">Port of Loading</div>
            <div class="v">{{ $header['port_of_loading'] ?: ($header['origin'] ?: '—') }}</div>
            <div class="k">Port of Discharge</div>
            <div class="v">{{ $header['port_of_discharge'] ?: ($header['destination'] ?: '—') }}</div>
            <div class="k">ETD / ETA</div>
            <div class="v">{{ $header['etd'] ?: '—' }} / {{ $header['eta'] ?: '—' }}</div>
          </div>
        </div>
        <div class="card">
          <h3>Container(s)</h3>
          <div class="kv">
            @forelse ($header['containers'] as $container)
              <div class="k">{{ $container['number'] }}</div>
              <div class="v">{{ $container['type'] }}{{ $container['seal'] ? ' — Seal '.$container['seal'] : '' }}{{ $container['line'] ? ' / '.$container['line'] : '' }}</div>
            @empty
              <div class="k">—</div>
              <div class="v">No container recorded on the master shipment</div>
            @endforelse
            <div class="k">Final Destination</div>
            <div class="v">{{ $header['destination'] ?: '—' }}</div>
          </div>
        </div>
      </section>

      <section class="items">
        <h3>Cargo by Client</h3>
        <table class="tbl" role="table" aria-label="Manifest">
          <thead>
            <tr>
              <th style="width:24px" class="center">#</th>
              <th>House Job</th>
              <th>HBL No</th>
              <th>Shipper</th>
              <th>Consignee</th>
              <th>Description of Goods</th>
              <th>HS Code</th>
              <th class="right">Pkgs</th>
              <th class="right">Gross Wt (kg)</th>
              <th class="right">CBM</th>
              <th>Marks &amp; Nos</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rows as $row)
              <tr class="{{ $row['first_line'] && !$loop->first ? 'client-start' : '' }}">
                <td class="center">{{ $row['first_line'] ? $row['seq'] : '' }}</td>
                <td class="mono">{{ $row['first_line'] ? $row['house_job'] : '' }}</td>
                <td class="mono">{{ $row['first_line'] ? ($row['hbl'] ?: '—') : '' }}</td>
                <td>{{ $row['first_line'] ? ($row['shipper'] ?: '—') : '' }}</td>
                <td>{{ $row['first_line'] ? ($row['consignee'] ?: '—') : '' }}</td>
                <td>{{ $row['description'] ?: '—' }}{{ $row['dg'] ? ' (DG)' : '' }}</td>
                <td>{{ $row['hs_code'] ?: '—' }}</td>
                <td class="right">{{ $row['packages'] ?: '—' }} {{ $row['package_type'] }}</td>
                <td class="right">{{ $row['gross_weight'] ? number_format($row['gross_weight'], 2) : '—' }}</td>
                <td class="right">{{ $row['cbm'] ? number_format($row['cbm'], 3) : '—' }}</td>
                <td>{{ $row['marks'] ?: '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="11" class="center">No clients attached to this consolidation yet.</td></tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <td colspan="7" class="right"><strong>TOTAL — {{ $totals['clients'] }} client(s)</strong></td>
              <td class="right"><strong>{{ number_format($totals['packages']) }}</strong></td>
              <td class="right"><strong>{{ number_format($totals['gross_weight'], 2) }}</strong></td>
              <td class="right"><strong>{{ number_format($totals['cbm'], 3) }}</strong></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </section>

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
          <div class="label">Date &amp; Stamp</div>
          <div class="line"></div><br>
          <div class="line"></div>
        </div>
      </section>

      <footer class="manifest__footer">
        <div>Weights and measurements as declared by the shippers. Generated by {{ auth()->user()->name }} {{ auth()->user()->surname }}.</div>
      </footer>

    </section>

@endsection
