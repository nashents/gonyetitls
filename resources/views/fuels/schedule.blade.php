@extends('layouts.jobcard')

@section('title')
    Fuel Order Schedule
@endsection

@section('extra-css')
    @include('fuels.partials.document-styles')
    <style>
      .skipped { max-width: 1024px; margin: 16px auto 0; padding: 10px 12px; border: 1px solid #fde68a; background: #fffbeb; border-radius: 10px; font-size: 13px; }
      @media print { .skipped { display: none; } }
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
  @endif

  @foreach ($groups as $fuels)
    @php
        $first = $fuels->first();
        $container = $first->container;
        $sourceHorse = $first->source_horse;
        $totalLitres = $fuels->sum(fn ($fuel) => (float) $fuel->quantity);
        $authorisers = App\Models\User::whereIn('id', $fuels->pluck('authorized_by_id')->filter()->unique())->get()
            ->map(fn ($user) => trim($user->name.' '.$user->surname))->implode(', ');
        $dates = $fuels->map(function ($fuel) {
            try { return \Carbon\Carbon::parse($fuel->date); } catch (\Throwable $e) { return null; }
        })->filter()->sort()->values();
        $dateRange = $dates->isEmpty() ? '—' : ($dates->first()->isSameDay($dates->last())
            ? $dates->first()->format('d M Y')
            : $dates->first()->format('d M Y').' – '.$dates->last()->format('d M Y'));
    @endphp

    <section class="doc" data-entity="fuel order schedule">

      @include('fuels.partials.document-header', [
          'title' => 'Fuel Order Schedule',
          'meta' => [
              'Station' => e($container ? $container->name : ($sourceHorse ? 'Truck '.$sourceHorse->identifier_label : '—')),
              'Collection' => e($dateRange),
              'Fuel Orders' => e($fuels->count()),
              'Total Quantity' => e(number_format($totalLitres, 2).' L'),
          ],
      ])

      <div class="notice">
        Each truck has its own fuel order number. Please record the <strong>Order #</strong> against each fill-up and quote it on your invoice or statement.
      </div>

      <div class="hr"></div>

      <section class="card">
        <h3>Fuel Order To</h3>
        @if ($container)
          <p><strong>{{ $container->name }}</strong></p>
          @if ($container->address) <p>{{ $container->address }}</p> @endif
          @if ($container->email) <p>{{ $container->email }}</p> @endif
        @elseif ($sourceHorse)
          <p><strong>Truck: {{ $sourceHorse->identifier_label }}</strong></p>
          <p>Truck-to-truck transfer</p>
        @endif
      </section>

      <div class="hr"></div>

      <table class="tbl" aria-label="Fuel Orders">
        <thead>
          <tr>
            <th class="center" style="width: 4%">#</th>
            <th style="width: 14%">Order #</th>
            <th style="width: 13%">Date</th>
            <th>Truck / Unit</th>
            <th>Driver</th>
            <th style="width: 9%">Fuel Type</th>
            <th class="right" style="width: 9%">Quantity</th>
            <th class="center" style="width: 7%">Full Tank</th>
            <th style="width: 15%">Driver Sign</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($fuels as $fuel)
            @php
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
              <td class="center">{{ $loop->iteration }}</td>
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
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6" class="right">Total</td>
            <td class="right">{{ number_format($totalLitres, 2) }} L</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>

      <section class="signatures">
        <div class="sig">
          <div class="label">Prepared By: {{ $preparedBy }} (Sign)</div>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Authorised By{{ $authorisers ? ': '.$authorisers : '' }} (Sign)</div>
          <div class="line"></div>
        </div>
        <div class="sig">
          <div class="label">Station Attendant (Name &amp; Sign)</div>
          <div class="line"></div>
        </div>
        <div class="sig label-only"><div class="label">Station (Stamp)</div></div>
      </section>

      <footer class="footer">
        <div>{{ ucfirst($company->name) }} — Fuel Order Schedule · {{ $container ? $container->name : '' }}</div>
        <div>Generated: {{ now()->format('Y-m-d H:i') }}</div>
      </footer>
    </section>
  @endforeach
@endsection
