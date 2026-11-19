@extends('layouts.jobcard')

@section('title')
    Fuel Order Schedule
@endsection

@section('extra-css')
    @include('fuels.partials.document-styles')
    <style>
      .skipped { max-width: 1024px; margin: 16px auto 0; padding: 10px 12px; border: 1px solid #fde68a; background: #fffbeb; border-radius: 10px; font-size: 13px; }
      .notice.compact { margin-top: 8px; padding: 6px 10px; font-size: 12px; }
      /* One compact table for every station: tight rows, allowed to run over pages. */
      .tbl.schedule th, .tbl.schedule td { padding: 4px 6px; }
      .tbl.schedule td.order { font-size: 12px; }
      .tbl.schedule tr.station th { background: #f1f5f9; font-size: 12px; text-align: left; border-top: 1.5pt solid var(--accent); }
      .tbl.schedule tr.station .station-meta { font-weight: 400; color: var(--muted); margin-left: 6px; }
      .tbl.schedule tr.subtotal td { background: #f8fafc; font-weight: 700; }
      .tbl.schedule tr.subtotal td.attendant { font-weight: 400; color: var(--muted); font-size: 11px; white-space: nowrap; }
      .tbl.schedule tfoot td { background: #eef2f7; font-size: 13px; }
      .signatures.once { grid-template-columns: repeat(3, 1fr); }
      @media print {
        .skipped { display: none; }
        .tbl.schedule { break-inside: auto; page-break-inside: auto; }
        .tbl.schedule tr.station { break-after: avoid; page-break-after: avoid; }
        .signatures.once { grid-template-columns: repeat(3, 1fr) !important; break-inside: avoid; page-break-inside: avoid; }
        .signatures.once .sig { min-height: 64px; }
      }
    </style>
@endsection

@section('content')
@php
    // Fuel dates are stored as free text ("2026-07-10", "2026-07-22T16:48", ...): show them all one way.
    $formatDate = function ($date) {
        try {
            $parsed = \Carbon\Carbon::parse($date);
            return preg_match('/\d{1,2}:\d{2}/', (string) $date) ? $parsed->format('d M Y H:i') : $parsed->format('d M Y');
        } catch (\Throwable $e) {
            return $date;
        }
    };
    $preparer = Auth::user()->employee;
    $preparedBy = $preparer ? $preparer->name.' '.$preparer->surname : Auth::user()->name.' '.Auth::user()->surname;

    // Every station goes on ONE document: one header, one table, one set of official sign-offs.
    $all = $groups->flatten(1);
    $grandLitres = $all->sum(fn ($fuel) => (float) $fuel->quantity);
    $authorisers = App\Models\User::whereIn('id', $all->pluck('authorized_by_id')->filter()->unique())->get()
        ->map(fn ($user) => trim($user->name.' '.$user->surname))->implode(', ');
    $dates = $all->map(function ($fuel) {
        try { return \Carbon\Carbon::parse($fuel->date); } catch (\Throwable $e) { return null; }
    })->filter()->sort()->values();
    $dateRange = $dates->isEmpty() ? '—' : ($dates->first()->isSameDay($dates->last())
        ? $dates->first()->format('d M Y')
        : $dates->first()->format('d M Y').' – '.$dates->last()->format('d M Y'));
    $stationName = function ($fuel) {
        if ($fuel->container) return $fuel->container->name;
        return $fuel->source_horse ? 'Truck '.$fuel->source_horse->identifier_label.' (truck-to-truck)' : '—';
    };
    $stationNames = $groups->map(fn ($fuels) => $stationName($fuels->first()))->values();
    $multiStation = $groups->count() > 1;
@endphp

  <div class="action-bar">
    <button class="btn" type="button" onclick="window.history.back()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Back
    </button>
    @if ($groups->isNotEmpty())
      <button class="btn" id="btnPrint" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" /></svg>
        Print
      </button>
    @endif
  </div>

  @if ($skipped->isNotEmpty())
    <div class="skipped">
      <strong>Left off the schedule ({{ $skipped->count() }}):</strong> only approved fuel orders go to a station.
      {{ $skipped->map(fn ($fuel) => $fuel->order_number.' ('.($fuel->authorization ?: 'pending').')')->implode(', ') }}
    </div>
  @endif
  @if ($groups->isEmpty())
    <div class="skipped">None of the selected fuel orders are approved, so there's nothing to schedule.</div>
  @else
    <section class="doc" data-entity="fuel order schedule">

      @include('fuels.partials.document-header', [
          'title' => 'Fuel Order Schedule',
          'meta' => [
              ($multiStation ? 'Stations' : 'Station') => e($multiStation ? $stationNames->implode(', ') : $stationNames->first()),
              'Collection' => e($dateRange),
              'Fuel Orders' => e($all->count()),
              'Total Quantity' => e(number_format($grandLitres, 2).' L'),
          ],
      ])

      <div class="notice compact">
        Each truck has its own fuel order number. Please record the <strong>Order #</strong> against each fill-up and quote it on your invoice or statement.
      </div>

      <table class="tbl schedule" aria-label="Fuel Orders" style="margin-top: 10px">
        <thead>
          <tr>
            <th class="center" style="width: 4%">#</th>
            <th style="width: 13%">Order #</th>
            <th style="width: 12%">Date</th>
            <th>Truck / Unit</th>
            <th>Driver</th>
            <th style="width: 8%">Fuel</th>
            <th class="right" style="width: 9%">Qty</th>
            <th class="center" style="width: 6%">Full</th>
            <th style="width: 15%">Driver Sign</th>
          </tr>
        </thead>
        <tbody>
          @php $row = 0; @endphp
          @foreach ($groups as $fuels)
            @php
                $first = $fuels->first();
                $container = $first->container;
                $stationLitres = $fuels->sum(fn ($fuel) => (float) $fuel->quantity);
                $stationMeta = $container ? collect([$container->address, $container->email])->filter()->implode(' · ') : '';
            @endphp
            <tr class="station">
              <th colspan="9">
                {{ $stationName($first) }}
                @if ($stationMeta) <span class="station-meta">{{ $stationMeta }}</span> @endif
              </th>
            </tr>
            @foreach ($fuels as $fuel)
              @php
                  $row++;
                  $driver = optional($fuel->driver)->employee ?? $fuel->employee;
                  if ($fuel->horse) {
                      $unit = trim($fuel->horse->identifier_label.' '.optional($fuel->horse->horse_make)->name.' '.optional($fuel->horse->horse_model)->name);
                  } elseif ($fuel->vehicle) {
                      $unit = $fuel->vehicle->identifier_label;
                  } elseif ($fuel->asset) {
                      $unit = trim(optional(optional($fuel->asset->product)->brand)->name.' '.optional($fuel->asset->product)->name);
                  } else {
                      $unit = $fuel->type;
                  }
              @endphp
              <tr>
                <td class="center">{{ $row }}</td>
                <td class="order">{{ $fuel->order_number }}</td>
                <td>{{ $formatDate($fuel->date) }}</td>
                <td>{{ $unit }}</td>
                <td>{{ $driver ? $driver->name.' '.$driver->surname : '—' }}</td>
                <td>{{ $fuel->container ? $fuel->container->fuel_type : $fuel->fuel_type }}</td>
                <td class="right"><strong>{{ $fuel->quantity ? (float) $fuel->quantity.' L' : '—' }}</strong></td>
                <td class="center">{{ $fuel->is_full_tank ? 'Yes' : 'No' }}</td>
                <td class="sign"></td>
              </tr>
            @endforeach
            @if ($multiStation)
              {{-- Each station's attendant signs their own block; the officials sign once at the bottom. --}}
              <tr class="subtotal">
                <td colspan="5" class="attendant">Station attendant (name &amp; sign): ________________________ &nbsp; Stamp:</td>
                <td class="right">{{ $fuels->count() }}</td>
                <td class="right">{{ number_format($stationLitres, 2) }} L</td>
                <td colspan="2"></td>
              </tr>
            @endif
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6" class="right">{{ $multiStation ? 'Grand Total' : 'Total' }}</td>
            <td class="right">{{ number_format($grandLitres, 2) }} L</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>

      <section class="signatures once">
        <div class="sig">
          <div class="label">Prepared By: {{ $preparedBy }} (Sign)</div>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Authorised By{{ $authorisers ? ': '.$authorisers : '' }} (Sign)</div>
          <div class="line"></div>
        </div>
        @if ($multiStation)
          <div class="sig">
            <div class="label">Checked By (Name &amp; Sign)</div>
            <div class="line"></div>
          </div>
        @else
          <div class="sig">
            <div class="label">Station Attendant (Name, Sign &amp; Stamp)</div>
            <div class="line"></div>
          </div>
        @endif
      </section>

      <footer class="footer">
        <div>{{ ucfirst($company->name) }} — Fuel Order Schedule</div>
        <div>Generated: {{ now()->format('Y-m-d H:i') }}</div>
      </footer>
    </section>
  @endif
@endsection
