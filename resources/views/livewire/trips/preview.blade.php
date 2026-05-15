<div>
@section('extra-css')
    <style>
      :root{
        --accent: {{$company->color}};
      }
      .tag { display:inline-block; padding:2px 8px; border-radius:999px; border:1px solid var(--line); font-size:12px; }
      .tag.planned    { background:#eff6ff; border-color:#bfdbfe; }
      .tag.in-transit { background:#fffbeb; border-color:#fde68a; }
      .tag.completed  { background:#ecfdf5; border-color:#a7f3d0; }
      .tag.cancelled  { background:#fef2f2; border-color:#fecaca; }
      .tag.paid       { background:#ecfdf5; border-color:#a7f3d0; }
      .tag.unpaid     { background:#fef2f2; border-color:#fecaca; }
      .tag.partial    { background:#fffbeb; border-color:#fde68a; }
      .loss           { color:#dc2626; font-weight:600; }
    </style>
@endsection

@php
  $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';

  $statusClass = match(strtolower($trip->trip_status ?? '')) {
    'in transit', 'in-transit' => 'in-transit',
    'completed'                => 'completed',
    'cancelled'                => 'cancelled',
    default                    => 'planned',
  };

  $payClass = match(strtolower($trip->payment_status ?? '')) {
    'paid'    => 'paid',
    'partial' => 'partial',
    default   => 'unpaid',
  };

  $user      = Auth::user();
  $deptNames = $user->employee?->departments?->pluck('name')->toArray() ?? [];
  $roleNames = $user->roles->pluck('name')->toArray();
  $showFinancials = !optional($user->employee?->company)->rates_managed_by_finance
                    || in_array('Finance', $deptNames)
                    || in_array('Super Admin', $roleNames);

  // trip -> trip_transport_orders -> transport_order -> customer
  $tripTransportOrders = $trip->trip_transport_orders ?? collect();

  $authorizer = App\Models\User::find($trip->authorized_by_id);
  $dn         = $trip->delivery_note;
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

  <section class="doc" data-entity="tripsheet">

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
        <h1>Trip Sheet</h1>
        <dl class="meta-grid">
          <dt>Trip #</dt>
          <dd class="mono">{{$trip->trip_number}}{{$trip->trip_ref ? '/'.$trip->trip_ref : ''}}</dd>

          <dt>Start Date</dt>
          <dd>{{preg_match($pattern, $trip->start_date) ? \Carbon\Carbon::parse($trip->start_date)->format('d M Y H:i') : $trip->start_date}}</dd>

          <dt>Trip Status</dt>
          <dd><span class="tag {{$statusClass}}">{{ucfirst($trip->trip_status ?? 'Planned')}}</span></dd>

          @if($trip->payment_status)
          <dt>Payment Status</dt>
          <dd><span class="tag {{$payClass}}">{{ucfirst($trip->payment_status)}}</span></dd>
          @endif
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== Trip Details ===== -->
    <section class="card">
      <h3>Trip Details</h3>
      <div class="kv" style="grid-template-columns: 180px 1fr 180px 1fr;">
        <div class="k">Trip #</div>
        <div class="v mono">{{$trip->trip_number}}{{$trip->trip_ref ? '/'.$trip->trip_ref : ''}}</div>

        <div class="k">Trip Type</div>
        <div class="v">{{$trip->trip_type?->name ?? '—'}}</div>

        @if($trip->manifest_number)
        <div class="k">Manifest #</div><div class="v mono">{{$trip->manifest_number}}</div>
        @endif

        @if($trip->cd3_number)
        <div class="k">CD3 #</div><div class="v mono">{{$trip->cd3_number}}</div>
        @endif

        @if($trip->cd1_number)
        <div class="k">CD1 #</div><div class="v mono">{{$trip->cd1_number}}</div>
        @endif

        @if($trip->trip_group)
        <div class="k">Trip Tracking</div><div class="v">{{$trip->trip_group->name}}</div>
        @endif

        <div class="k">Transporter</div>
        <div class="v">{{$trip->transporter?->name ?? '—'}}</div>

        @if(isset($trip->broker))
        <div class="k">Broker</div><div class="v">{{$trip->broker->name}}</div>
        @endif
      </div>
    </section>

    <!-- ===== Transport Orders ===== -->
    @if($tripTransportOrders->count() > 0)
    <section class="card" style="margin-top:12px;">
      <h3>Transport Orders</h3>
      <table class="tbl" role="table" aria-label="Transport Orders">
        <thead>
          <tr>
            <th class="center" style="width:4%">#</th>
            <th style="width:14%">Order #</th>
            <th style="width:18%">Customer</th>
            <th style="width:12%">Cargo</th>
            <th class="right" style="width:9%">Weight</th>
            <th class="right" style="width:9%">Quantity</th>
            <th style="width:10%">From</th>
            <th style="width:10%">To</th>
            @if($showFinancials)
            <th class="right" style="width:12%">Freight</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($tripTransportOrders as $tto)
          @php $to = $tto->transport_order; @endphp
          <tr>
            <td class="center">{{$loop->iteration}}</td>
            <td class="mono">{{$to?->order_number ?? '—'}}</td>
            <td>{{$to?->customer?->name ?? '—'}}</td>
            <td>{{$to?->cargo?->name ?? $to?->cargo_description ?? '—'}}</td>
            <td class="right">{{$to?->weight ? $to->weight.' T' : '—'}}</td>
            <td class="right">{{$to?->quantity ? $to->quantity.' '.($to->measurement ?? '') : '—'}}</td>
            <td>
              @php $fD = $to?->fromDestination; @endphp
              {{$fD ? optional($fD->country)->name.' '.$fD->city : '—'}}
            </td>
            <td>
              @php $tD = $to?->toDestination; @endphp
              {{$tD ? optional($tD->country)->name.' '.$tD->city : '—'}}
            </td>
            @if($showFinancials)
            <td class="right">
              {{$to?->currency?->symbol}}{{$to?->freight ? number_format($to->freight, 2) : '—'}}
            </td>
            @endif
          </tr>
          @endforeach
        </tbody>
        @if($showFinancials && $tripTransportOrders->count() > 1)
        <tfoot>
          <tr>
            <td colspan="8" class="right"><strong>Total Freight</strong></td>
            <td class="right">
              <strong>
                {{$tripTransportOrders->first()?->transport_order?->currency?->symbol}}
                {{number_format($tripTransportOrders->sum(fn($tto) => $tto->transport_order?->freight ?? 0), 2)}}
              </strong>
            </td>
          </tr>
        </tfoot>
        @endif
      </table>
    </section>
    @endif

    <!-- ===== Equipment & Driver ===== -->
    <section class="grid-2" style="margin-top:12px;">

      <div class="card">
        <h3>Equipment</h3>
        <div class="kv">
          @if($trip->horse)
            <div class="k">Horse</div>
            <div class="v">
              {{optional($trip->horse->horse_make)->name}} {{optional($trip->horse->horse_model)->name}}
              {{$trip->horse->registration_number}}
            </div>
            <div class="k">Fleet #</div>
            <div class="v">{{$trip->horse->fleet_number ?? '—'}}</div>
          @elseif($trip->vehicle)
            <div class="k">Vehicle</div>
            <div class="v">
              {{optional($trip->vehicle->vehicle_make)->name}} {{optional($trip->vehicle->vehicle_model)->name}}
              {{$trip->vehicle->registration_number}}
            </div>
          @endif

          @if($trip->trailers->count() > 0)
          <div class="k">Trailer(s)</div>
          <div class="v">
            @foreach($trip->trailers as $trailer)
              {{$trailer->make}} {{$trailer->model}} {{$trailer->registration_number}}
              @if($trailer->fleet_number) | {{$trailer->fleet_number}} @endif<br>
            @endforeach
          </div>
          @endif
        </div>
      </div>

      <div class="card">
        <h3>Driver</h3>
        <div class="kv">
          <div class="k">Driver Name</div>
          <div class="v">
            {{$trip->driver?->employee?->name ?? '—'}} {{$trip->driver?->employee?->surname ?? ''}}
          </div>

          <div class="k">ID Number</div>
          <div class="v">{{$trip->driver?->employee?->idnumber ?? '—'}}</div>

          @if($trip->notes)
          <div class="k">Instructions</div>
          <div class="v">{{$trip->notes}}</div>
          @endif
        </div>
      </div>

    </section>

    <!-- ===== Route & Schedule ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Route &amp; Schedule</h3>
      <div class="kv" style="grid-template-columns: 180px 1fr 180px 1fr;">

        @php
          $fromDest = App\Models\Destination::find($trip->from);
          $toDest   = App\Models\Destination::find($trip->to);
        @endphp

        <div class="k">Origin</div>
        <div class="v">{{$fromDest ? optional($fromDest->country)->name.' '.$fromDest->city : '—'}}</div>

        <div class="k">Destination</div>
        <div class="v">{{$toDest ? optional($toDest->country)->name.' '.$toDest->city : '—'}}</div>

        @if($trip->loading_point)
        <div class="k">Loading Point</div><div class="v">{{$trip->loading_point->name}}</div>
        @endif

        @if($trip->offloading_point)
        <div class="k">Offloading Point</div><div class="v">{{$trip->offloading_point->name}}</div>
        @endif

        @if($trip->route)
        <div class="k">Route</div><div class="v">{{$trip->route->name}}</div>
        @endif

        @if($trip->distance)
        <div class="k">Distance</div><div class="v">{{$trip->distance}} km</div>
        @endif

        <div class="k">Start Date</div>
        <div class="v">{{preg_match($pattern, $trip->start_date) ? \Carbon\Carbon::parse($trip->start_date)->format('d M Y H:i') : $trip->start_date}}</div>

        @if($trip->end_date)
        <div class="k">Est. End Date</div>
        <div class="v">{{preg_match($pattern, $trip->end_date) ? \Carbon\Carbon::parse($trip->end_date)->format('d M Y H:i') : $trip->end_date}}</div>
        @endif

        @if(preg_match($pattern, $trip->start_date) && $trip->end_date && preg_match($pattern, $trip->end_date))
        <div class="k">Standard Duration</div>
        <div class="v">{{\Carbon\Carbon::parse($trip->start_date)->diffInDays($trip->end_date)}} Day(s)</div>
        @endif

        @if($trip->borders->count() > 0)
        <div class="k">Borders</div>
        <div class="v">@foreach($trip->borders as $b) {{$b->name}}<br> @endforeach</div>
        @endif

        @if($trip->truck_stops->count() > 0)
        <div class="k">Truck Stops</div>
        <div class="v">@foreach($trip->truck_stops as $s) {{$s->name}} {{$s->rating}}<br> @endforeach</div>
        @endif

        @if($trip->clearing_agents->count() > 0)
        <div class="k">Clearing Agents</div>
        <div class="v">
          @foreach($trip->clearing_agents as $a)
            {{$a->name}} · {{$a->email}} · {{$a->phonenumber}}<br>
          @endforeach
        </div>
        @endif

      </div>
    </section>

    <!-- ===== Fuel Orders ===== -->
    @if($trip->fuels->where('authorization','approved')->count() > 0)
    <section class="card" style="margin-top:12px;">
      <h3>Fuel Orders</h3>
      <table class="tbl" role="table" aria-label="Fuel Orders">
        <thead>
          <tr>
            <th class="center" style="width:4%">#</th>
            <th>Order #</th>
            <th>Type</th>
            <th>Container</th>
            <th>Fuel Type</th>
            <th class="right">Quantity</th>
          </tr>
        </thead>
        <tbody>
          @foreach($trip->fuels->where('authorization','approved') as $fuel)
          <tr>
            <td class="center">{{$loop->iteration}}</td>
            <td class="mono">{{$fuel->order_number}}</td>
            <td>{{$fuel->fillup == 1 ? 'Initial' : 'Top-up'}}</td>
            <td>{{$fuel->container?->name ?? '—'}}</td>
            <td>{{$fuel->container?->fuel_type ?? '—'}}</td>
            <td class="right">{{$fuel->quantity ? $fuel->quantity.'L' : '—'}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </section>
    @endif

    <!-- ===== Loading / Offloading & Losses ===== -->
    @if($dn && ($dn->loaded_weight || $dn->loaded_quantity || $dn->loaded_litreage || $dn->loaded_litreage_at_20 || $dn->loaded_freight))
    <section class="card" style="margin-top:12px;">
      <h3>Loading / Offloading &amp; Losses</h3>
      <table class="tbl" role="table" aria-label="Loading Offloading">
        <thead>
          <tr>
            <th style="width:28%">Measure</th>
            <th class="right">Scheduled</th>
            <th class="right">Loaded</th>
            <th class="right">Offloaded</th>
            <th class="right">Loss</th>
          </tr>
        </thead>
        <tbody>

          @if($trip->weight || $dn->loaded_weight || $dn->offloaded_weight)
          @php $wLoss = ($dn->loaded_weight > 0 && $dn->offloaded_weight > 0) ? $dn->loaded_weight - $dn->offloaded_weight : null; @endphp
          <tr>
            <td>Weight (Tons)</td>
            <td class="right">{{$trip->weight ?? '—'}}</td>
            <td class="right">{{$dn->loaded_weight ?? '—'}}</td>
            <td class="right">{{$dn->offloaded_weight ?? '—'}}</td>
            <td class="right">@isset($wLoss)<span class="{{$wLoss > 0 ? 'loss' : ''}}">{{$wLoss}}</span>@else —@endisset</td>
          </tr>
          @endif

          @if($trip->quantity || $dn->loaded_quantity || $dn->offloaded_quantity)
          @php $qLoss = ($dn->loaded_quantity > 0 && $dn->offloaded_quantity > 0) ? $dn->loaded_quantity - $dn->offloaded_quantity : null; @endphp
          <tr>
            <td>Quantity ({{$trip->measurement}})</td>
            <td class="right">{{$trip->quantity ?? '—'}}</td>
            <td class="right">{{$dn->loaded_quantity ?? '—'}}</td>
            <td class="right">{{$dn->offloaded_quantity ?? '—'}}</td>
            <td class="right">@isset($qLoss)<span class="{{$qLoss > 0 ? 'loss' : ''}}">{{$qLoss}}</span>@else —@endisset</td>
          </tr>
          @endif

          @if($trip->litreage || $dn->loaded_litreage || $dn->offloaded_litreage)
          @php $lLoss = ($dn->loaded_litreage > 0 && $dn->offloaded_litreage > 0) ? $dn->loaded_litreage - $dn->offloaded_litreage : null; @endphp
          <tr>
            <td>Litreage Ambient ({{$trip->measurement}})</td>
            <td class="right">{{$trip->litreage ?? '—'}}</td>
            <td class="right">{{$dn->loaded_litreage ?? '—'}}</td>
            <td class="right">{{$dn->offloaded_litreage ?? '—'}}</td>
            <td class="right">@isset($lLoss)<span class="{{$lLoss > 0 ? 'loss' : ''}}">{{$lLoss}}</span>@else —@endisset</td>
          </tr>
          @endif

          @if($trip->litreage_at_20 || $dn->loaded_litreage_at_20 || $dn->offloaded_litreage_at_20)
          @php $l20Loss = ($dn->loaded_litreage_at_20 > 0 && $dn->offloaded_litreage_at_20 > 0) ? $dn->loaded_litreage_at_20 - $dn->offloaded_litreage_at_20 : null; @endphp
          <tr>
            <td>Litreage @ 20°C ({{$trip->measurement}})</td>
            <td class="right">{{$trip->litreage_at_20 ?? '—'}}</td>
            <td class="right">{{$dn->loaded_litreage_at_20 ?? '—'}}</td>
            <td class="right">{{$dn->offloaded_litreage_at_20 ?? '—'}}</td>
            <td class="right">@isset($l20Loss)<span class="{{$l20Loss > 0 ? 'loss' : ''}}">{{$l20Loss}}</span>@else —@endisset</td>
          </tr>
          @endif

          @if($dn->loaded_freight || $dn->offloaded_freight)
          @php
            $fLoss = ($dn->loaded_freight > 0 && $dn->offloaded_freight > 0) ? $dn->loaded_freight - $dn->offloaded_freight : null;
            $sym   = $trip->currency?->symbol;
          @endphp
          <tr>
            <td>Freight ({{$trip->currency?->name}})</td>
            <td class="right">—</td>
            <td class="right">{{$sym}}{{number_format($dn->loaded_freight, 2)}}</td>
            <td class="right">{{$sym}}{{number_format($dn->offloaded_freight, 2)}}</td>
            <td class="right">@isset($fLoss)<span class="{{$fLoss > 0 ? 'loss' : ''}}">{{$sym}}{{number_format($fLoss, 2)}}</span>@else —@endisset</td>
          </tr>
          @endif

        </tbody>
      </table>
    </section>
    @endif

    <!-- ===== Trip Expenses ===== -->
    @if($trip->trip_expenses->count() > 0)
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
          @foreach($trip->trip_expenses as $tripExpense)
          @php
            $isAllowance = $tripExpense->allowance_id && $tripExpense->allowance;
            $typeLabel   = $isAllowance ? 'Allowance' : 'Expense';
            $typeClass   = $isAllowance ? 'planned' : '';
            $description = $isAllowance ? ($tripExpense->allowance->name ?? '—') : ($tripExpense->expense?->name ?? '—');
          @endphp
          <tr>
            <td class="center">{{$loop->iteration}}</td>
            <td><span class="tag {{$typeClass}}">{{$typeLabel}}</span></td>
            <td>{{$description}}</td>
            <td>{{$tripExpense->currency?->name ?? '—'}}</td>
            <td>{{$tripExpense->payment_method?->name ?? '—'}}</td>
            <td class="right">{{$tripExpense->currency?->symbol}}{{number_format($tripExpense->amount, 2)}}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="right"><strong>Total</strong></td>
            <td class="right"><strong>{{number_format($trip->trip_expenses->sum('amount'), 2)}}</strong></td>
          </tr>
        </tfoot>
      </table>
    </section>
    @endif

    <!-- ===== Trip-level Financials ===== -->
    @if($showFinancials && ($trip->currency || $trip->rate || $trip->freight))
    <section class="card" style="margin-top:12px;">
      <h3>Trip Financials</h3>
      <div class="kv">
        @if($trip->currency)
        <div class="k">Currency</div><div class="v">{{$trip->currency->name}}</div>
        @endif
        @if($trip->rate)
        <div class="k">Rate</div><div class="v">{{$trip->currency?->symbol}}{{number_format($trip->rate, 2)}}</div>
        @endif
        @if($trip->freight)
        <div class="k">Freight</div><div class="v">{{$trip->currency?->symbol}}{{number_format($trip->freight, 2)}}</div>
        @endif
      </div>
    </section>
    @endif

    <!-- ===== Comments ===== -->
    @if($trip->comments)
    <section class="card" style="margin-top:12px;">
      <h3>Comments / Instructions</h3>
      <div class="notes">{{$trip->comments}}</div>
    </section>
    @endif

    <!-- ===== Authorization ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Authorization</h3>
      <div class="kv" style="grid-template-columns: 140px 1fr 140px 1fr 140px 1fr;">
        <div class="k">Checked By</div>
        <div class="v">{{$trip->user?->employee?->name ?? '—'}} {{$trip->user?->employee?->surname ?? ''}}</div>

        <div class="k">Authorized By</div>
        <div class="v">
          {{$authorizer?->employee?->name ?? ($authorizer?->name ?? '—')}}
          {{$authorizer?->employee?->surname ?? ''}}
        </div>

        <div class="k">Trip Status</div>
        <div class="v">
          <span class="tag {{$statusClass}}">{{ucfirst($trip->trip_status ?? 'Planned')}}</span>
          @if($trip->payment_status)
            &nbsp;<span class="tag {{$payClass}}">{{ucfirst($trip->payment_status)}}</span>
          @endif
        </div>
      </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig"><div class="label">Driver (Name &amp; Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Dispatcher (Name &amp; Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Authorizing Officer (Name &amp; Sign)</div><div class="line"></div></div>
      <div class="sig label-only"><div class="label">Customer / Consignee (Stamp)</div></div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:12px; color:var(--muted);">
      <div>{{ucfirst($company->name)}} — Trip Sheet for {{$trip->trip_number}}</div>
      <div>Generated: <span id="printTimestamp" data-field="generated.at"></span></div>
    </footer>

  </section>
</div>
