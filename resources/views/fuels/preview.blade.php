@extends('layouts.jobcard')

@section('title')
    Fuel Order {{ $fuel->order_number }}
@endsection

@section('extra-css')
    @include('fuels.partials.document-styles')
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

    // Trip-linked orders carry driver_id; others only the employee picked on the form.
    $driver = optional($fuel->driver)->employee ?? $fuel->employee;
    $authoriser = App\Models\User::find($fuel->authorized_by_id);
    $checkedBy = optional($fuel->user)->employee;

    if ($fuel->horse) {
        $unitLabel = 'Horse';
        $unit = trim($fuel->horse->identifier_label.' '.optional($fuel->horse->horse_make)->name.' '.optional($fuel->horse->horse_model)->name);
    } elseif ($fuel->vehicle) {
        $unitLabel = 'Vehicle';
        $unit = trim($fuel->vehicle->identifier_label.' '.optional($fuel->vehicle->vehicle_make)->name.' '.optional($fuel->vehicle->vehicle_model)->name);
    } elseif ($fuel->asset) {
        $unitLabel = 'Asset';
        $unit = trim(optional(optional($fuel->asset->product)->brand)->name.' '.optional($fuel->asset->product)->name);
    } else {
        $unitLabel = 'Unit';
        $unit = $fuel->type ?: '—';
    }

    $fuelType = $fuel->container ? $fuel->container->fuel_type : $fuel->fuel_type;
@endphp

  <div class="action-bar">
    <button class="btn" type="button" onclick="window.history.back()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Back
    </button>
    <button class="btn" id="btnPrint" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" /></svg>
      Print
    </button>
  </div>

  <section class="doc" data-entity="fuel order {{ $fuel->order_number }}">

    @include('fuels.partials.document-header', [
        'title' => 'Fuel Order',
        'meta' => [
            'Order #' => '<span class="order-no">'.e($fuel->order_number).'</span>',
            'Collection Date' => e($formatDate($fuel->date)),
            'Status' => e(ucfirst($fuel->authorization ?: 'pending')),
        ],
    ])

    <div class="notice">
      Please record <strong class="order-ref">{{ $fuel->order_number }}</strong> against this fill-up and quote it on your invoice or statement.
    </div>

    <div class="hr"></div>

    <div class="grid-2">
      <section class="card">
        <h3>Fuel Order To</h3>
        @if ($container)
          <p><strong>{{ $container->name }}</strong></p>
          @if ($container->address) <p>{{ $container->address }}</p> @endif
          @if ($container->email) <p>{{ $container->email }}</p> @endif
        @elseif ($source_horse)
          <p><strong>Truck: {{ $source_horse->identifier_label }}</strong></p>
          <p>Truck-to-truck transfer</p>
        @else
          <p>—</p>
        @endif
      </section>

      <section class="card">
        <h3>{{ $unitLabel }} &amp; Driver</h3>
        <div class="kv">
          <div class="k">{{ $unitLabel }}</div><div class="v"><strong>{{ $unit }}</strong></div>
          <div class="k">Driver</div><div class="v">{{ $driver ? $driver->name.' '.$driver->surname : '—' }}</div>
          @if ($fuel->odometer)
            <div class="k">Mileage</div><div class="v">{{ $fuel->odometer }}</div>
          @endif
        </div>
      </section>
    </div>

    <div class="hr"></div>

    <table class="tbl" aria-label="Fuel">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Fuel Type</th>
          <th class="right">Quantity</th>
          <th class="center">Full Tank</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="order">{{ $fuel->order_number }}</td>
          <td>{{ $fuelType ?: '—' }}</td>
          <td class="right"><strong>{{ $fuel->quantity ? (float) $fuel->quantity.' L' : '—' }}</strong></td>
          <td class="center">{{ $fuel->is_full_tank ? 'Yes' : 'No' }}</td>
        </tr>
      </tbody>
    </table>

    @if ($fuel->comments)
      <section class="card" style="margin-top: 12px;">
        <h3>Comments</h3>
        <p>{{ $fuel->comments }}</p>
      </section>
    @endif

    <section class="signatures">
      <div class="sig">
        <div class="label">Driver{{ $driver ? ': '.$driver->name.' '.$driver->surname : '' }} (Sign)</div>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">Station Attendant (Name &amp; Sign)</div>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">
          Checked By{{ $checkedBy ? ': '.$checkedBy->name.' '.$checkedBy->surname : '' }}
          @if ($authoriser)<br>Authorised By: {{ $authoriser->name }} {{ $authoriser->surname }}@endif
        </div>
        <div class="line"></div>
      </div>
      <div class="sig label-only"><div class="label">Station (Stamp)</div></div>
    </section>

    <footer class="footer">
      <div>{{ ucfirst($company->name) }} — Fuel Order {{ $fuel->order_number }}</div>
      <div>Generated: <span id="printTimestamp">{{ now()->format('Y-m-d H:i') }}</span></div>
    </footer>
  </section>
@endsection
