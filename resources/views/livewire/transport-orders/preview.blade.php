<div>
@section('extra-css')
    <style>
      :root{
        --accent: {{$company->color}};
      }
      .tag { display:inline-block; padding:2px 8px; border-radius:999px; border:1px solid var(--line); font-size:12px; }
      .tag.allowance { background:#eff6ff; border-color:#bfdbfe; }
    </style>
@endsection

@php
  $pattern = '/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/';

  // Resolve From routes
  $fromRoutes = collect();
  if ($transport_order->trip_origins && $transport_order->trip_origins->count()) {
      $fromRoutes = $transport_order->trip_origins
          ->map(function ($trip_origin) {
              $from         = $trip_origin->destination
                  ? trim(($trip_origin->destination->country?->name ?? '') . ' ' . ($trip_origin->destination->city ?? ''))
                  : null;
              $loadingPoint = $trip_origin->loading_point?->name;
              return [
                  'label' => trim(($from ?? '') . ($loadingPoint ? ' - ' . $loadingPoint : ''), ' -'),
                  'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
              ];
          })
          ->filter(fn ($item) => !empty($item['label']))
          ->unique('key')
          ->values();
  } else {
      $from         = $transport_order->fromDestination
          ? trim(($transport_order->fromDestination->country?->name ?? '') . ' ' . ($transport_order->fromDestination->city ?? ''))
          : null;
      $loadingPoint = $transport_order->loading_point?->name;
      $label        = trim(($from ?? '') . ($loadingPoint ? ' - ' . $loadingPoint : ''), ' -');
      if (!empty($label)) {
          $fromRoutes = collect([['label' => $label, 'key' => md5(($from ?? '') . '|' . ($loadingPoint ?? ''))]]);
      }
  }

  // Resolve To routes
  $toRoutes = collect();
  if ($transport_order->trip_destinations && $transport_order->trip_destinations->count()) {
      $toRoutes = $transport_order->trip_destinations
          ->map(function ($trip_destination) {
              $to              = $trip_destination->destination
                  ? trim(($trip_destination->destination->country?->name ?? '') . ' ' . ($trip_destination->destination->city ?? ''))
                  : null;
              $offloadingPoint = $trip_destination->offloading_point?->name;
              return [
                  'label' => trim(($to ?? '') . ($offloadingPoint ? ' - ' . $offloadingPoint : ''), ' -'),
                  'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
              ];
          })
          ->filter(fn ($item) => !empty($item['label']))
          ->unique('key')
          ->values();
  } else {
      $to              = $transport_order->toDestination
          ? trim(($transport_order->toDestination->country?->name ?? '') . ' ' . ($transport_order->toDestination->city ?? ''))
          : null;
      $offloadingPoint = $transport_order->offloading_point?->name;
      $label           = trim(($to ?? '') . ($offloadingPoint ? ' - ' . $offloadingPoint : ''), ' -');
      if (!empty($label)) {
          $toRoutes = collect([['label' => $label, 'key' => md5(($to ?? '') . '|' . ($offloadingPoint ?? ''))]]);
      }
  }

  $authorizer = App\Models\User::find($transport_order->authorized_by_id ?? null);
@endphp

  <!-- Action bar -->
  <div class="action-bar">
    <button class="btn" type="button" onclick="window.history.back()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Back
    </button>
    <button class="btn" type="button" onclick="window.print()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" /></svg>
      Print
    </button>
   
  </div>

  <section class="doc" data-entity="transport-order">

    <!-- ===== Header ===== -->
    <header class="header">
      <div class="brand">
        <img class="brand__logo" src="{{asset('images/uploads/'.$company->logo)}}" alt="Company Logo" onerror="this.style.display='none'" />
        <div>
          <div class="brand__name">{{$company->name}}</div>
          <div class="brand__meta">
            <span>{{$company->street_address}} {{$company->suburb}} {{$company->city}} {{$company->country}}</span><br />
            Tel: <span>{{$company->phonenumber}}</span>
            @if($company->second_phonenumber) · {{$company->second_phonenumber}} @endif
            @if($company->third_phonenumber)  · {{$company->third_phonenumber}}  @endif
            · Email: <span>{{$company->email}}</span>
            @if($company->second_email) · {{$company->second_email}} @endif
          </div>
        </div>
      </div>

      <aside class="meta">
        <h1>Transportation Order</h1>
        <dl class="meta-grid">
          <dt>Order #</dt>
          <dd class="mono">{{$transport_order->transport_order_number}}{{$transport_order->trip_ref ? '/'.$transport_order->trip_ref : ''}}</dd>

          <dt>Date</dt>
          <dd>{{$transport_order->date ? \Carbon\Carbon::parse($transport_order->date)->format('d M Y') : '—'}}</dd>

          @if($transport_order->manifest_number)
          <dt>Manifest #</dt>
          <dd class="mono">{{$transport_order->manifest_number}}</dd>
          @endif

          @if($transport_order->cd3_number)
          <dt>CD3 #</dt>
          <dd class="mono">{{$transport_order->cd3_number}}</dd>
          @endif
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== Two-column body ===== -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:0;">

      <!-- LEFT COLUMN -->
      <div style="display:flex; flex-direction:column; gap:10px;">

        <!-- Customer / Consignee -->
        <div class="card">
          <h3>Customer / Consignee</h3>
          <div class="kv">
            @if($transport_order->customer)
            <div class="k">Customer</div>
            <div class="v">{{$transport_order->customer->name}}</div>
            @if($transport_order->customer->street_address || $transport_order->customer->city)
            <div class="k">Address</div>
            <div class="v">
              {{$transport_order->customer->street_address}} {{$transport_order->customer->suburb}}
              {{$transport_order->customer->city}} {{$transport_order->customer->country}}
            </div>
            @endif
            @if($transport_order->customer->email)
            <div class="k">Email</div>
            <div class="v">{{$transport_order->customer->email}}</div>
            @endif
            @endif
            @if($transport_order->consignee)
            <div class="k">Consignee</div>
            <div class="v">{{$transport_order->consignee->name}}</div>
            @endif
          </div>
        </div>

        <!-- Route -->
        <div class="card">
          <h3>Route</h3>
          <div class="kv">
            <div class="k">From</div>
            <div class="v">
              @forelse($fromRoutes as $route)
                {{$route['label']}}@if(!$loop->last)<br>@endif
              @empty —
              @endforelse
            </div>

            <div class="k">To</div>
            <div class="v">
              @forelse($toRoutes as $route)
                {{$route['label']}}@if(!$loop->last)<br>@endif
              @empty —
              @endforelse
            </div>

            @if($transport_order->loading_point)
            <div class="k">Loading Point</div>
            <div class="v">{{$transport_order->loading_point->name}}</div>
            @endif

            @if($transport_order->offloading_point)
            <div class="k">Offloading Point</div>
            <div class="v">{{$transport_order->offloading_point->name}}</div>
            @endif
          </div>
        </div>

        <!-- Cargo -->
        <div class="card">
          <h3>Cargo</h3>
          <div class="kv">
            <div class="k">Cargo</div>
            <div class="v">{{$transport_order->cargo?->name ?? '—'}}</div>

            @if($transport_order->weight)
            <div class="k">Weight (Gross)</div>
            <div class="v">{{$transport_order->weight}} Tons</div>
            @endif

            @if($transport_order->net_weight)
            <div class="k">Net Weight</div>
            <div class="v">{{$transport_order->net_weight}} Tons</div>
            @endif

            @if($transport_order->quantity)
            <div class="k">Quantity</div>
            <div class="v">{{$transport_order->quantity}} {{$transport_order->unit_of_measure}}</div>
            @endif

            @if($transport_order->litreage)
            <div class="k">Litreage (Ambient)</div>
            <div class="v">{{$transport_order->litreage}} {{$transport_order->unit_of_measure}}</div>
            @endif

            @if($transport_order->litreage_at_20)
            <div class="k">Litreage @ 20°C</div>
            <div class="v">{{$transport_order->litreage_at_20}} {{$transport_order->unit_of_measure}}</div>
            @endif

            @if($transport_order->temparature)
            <div class="k">Temperature</div>
            <div class="v">{{$transport_order->temparature}}</div>
            @endif
          </div>
        </div>

      </div>{{-- end left --}}

      <!-- RIGHT COLUMN -->
      <div style="display:flex; flex-direction:column; gap:10px;">

        <!-- Order Details -->
        <div class="card">
          <h3>Order Details</h3>
          <div class="kv">
            <div class="k">Order #</div>
            <div class="v mono">{{$transport_order->transport_order_number}}{{$transport_order->trip_ref ? '/'.$transport_order->trip_ref : ''}}</div>

            <div class="k">Trip Type</div>
            <div class="v">{{$transport_order->trip_type?->name ?? '—'}}</div>

            @if($transport_order->manifest_number)
            <div class="k">Manifest #</div><div class="v mono">{{$transport_order->manifest_number}}</div>
            @endif

            @if($transport_order->cd3_number)
            <div class="k">CD3 #</div><div class="v mono">{{$transport_order->cd3_number}}</div>
            @endif

            @if($transport_order->cd1_number)
            <div class="k">CD1 #</div><div class="v mono">{{$transport_order->cd1_number}}</div>
            @endif

            @if($transport_order->bill_of_entry)
            <div class="k">Bill of Entry</div><div class="v mono">{{$transport_order->bill_of_entry}}</div>
            @endif

            @if($transport_order->seal_numbers)
            <div class="k">Seal Numbers</div><div class="v mono">{{$transport_order->seal_numbers}}</div>
            @endif

            <div class="k">Issue Date</div>
            <div class="v">
              {{preg_match($pattern, $transport_order->created_at) ? \Carbon\Carbon::parse($transport_order->created_at)->format('d M Y H:i') : $transport_order->created_at}}
            </div>
          </div>
        </div>

        <!-- Financials -->
        @if($transport_order->currency || $transport_order->rate || $transport_order->freight)
        <div class="card">
          <h3>Financials</h3>
          <div class="kv">
            @if($transport_order->currency)
            <div class="k">Currency</div>
            <div class="v">{{$transport_order->currency->name}}</div>
            @endif

            @if($transport_order->rate)
            <div class="k">Rate</div>
            <div class="v">{{$transport_order->currency?->symbol}}{{number_format($transport_order->rate, 2)}}</div>
            @endif

            @if($transport_order->freight)
            <div class="k">Freight</div>
            <div class="v">{{$transport_order->currency?->symbol}}{{number_format($transport_order->freight, 2)}}</div>
            @endif

            @if($transport_order->freight_calculation_method)
            <div class="k">Calc Method</div>
            <div class="v">{{$transport_order->freight_calculation_method}}</div>
            @endif
          </div>
        </div>
        @endif

      </div>{{-- end right --}}

    </div>{{-- end two-column grid --}}

    <!-- ===== Trip Expenses ===== -->
    @if($transport_order->trip && $transport_order->trip->trip_expenses->count() > 0)
    <section class="card" style="margin-top:12px;">
      <h3>Trip Expenses &amp; Allowances</h3>
      <table class="tbl" role="table" aria-label="Trip Expenses">
        <thead>
          <tr>
            <th class="center" style="width:4%">#</th>
            <th style="width:10%">Type</th>
            <th>Description</th>
            <th style="width:16%">Currency</th>
            <th style="width:16%">Payment Method</th>
            <th class="right" style="width:14%">Amount</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transport_order->trip->trip_expenses as $trip_expense)
          @php
            $isAllowance = $trip_expense->allowance_id && $trip_expense->allowance;
            $typeLabel   = $isAllowance ? 'Allowance' : 'Expense';
            $description = $isAllowance ? ($trip_expense->allowance->name ?? '—') : ($trip_expense->expense?->name ?? '—');
          @endphp
          <tr>
            <td class="center">{{$loop->iteration}}</td>
            <td><span class="tag {{$isAllowance ? 'allowance' : ''}}">{{$typeLabel}}</span></td>
            <td>{{$description}}</td>
            <td>{{$trip_expense->currency?->name ?? '—'}}</td>
            <td>{{$trip_expense->payment_method?->name ?? '—'}}</td>
            <td class="right">{{$trip_expense->currency?->symbol}}{{number_format($trip_expense->amount, 2)}}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="right"><strong>Total</strong></td>
            <td class="right"><strong>{{number_format($transport_order->trip->trip_expenses->sum('amount'), 2)}}</strong></td>
          </tr>
        </tfoot>
      </table>
    </section>
    @endif

    <!-- ===== Authorization ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Authorization</h3>
      <div class="kv" style="grid-template-columns: 140px 1fr 140px 1fr;">
        <div class="k">Checked By</div>
        <div class="v">
          {{$transport_order->user?->employee?->name ?? ($transport_order->user?->name ?? '—')}}
          {{$transport_order->user?->employee?->surname ?? ''}}
        </div>

        <div class="k">Authorized By</div>
        <div class="v">
          {{$authorizer?->employee?->name ?? ($authorizer?->name ?? '—')}}
          {{$authorizer?->employee?->surname ?? ''}}
        </div>
      </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig"><div class="label">Prepared By (Name &amp; Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Authorized By (Name &amp; Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Driver (Name &amp; Sign)</div><div class="line"></div></div>
      <div class="sig label-only"><div class="label">Customer / Consignee (Stamp)</div></div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:12px; color:var(--muted);">
      <div>{{ucfirst($company->name)}} — Transportation Order {{$transport_order->transport_order_number}}</div>
      <div>Generated: <span id="printTimestamp" data-field="generated.at"></span></div>
    </footer>

  </section>
</div>
