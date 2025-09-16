<div>
@section('extra-css')
    <style>
      :root{
      --accent: {{$company->color}};          /* tweak to match your brand */
    }
    .tag { display:inline-block; padding:2px 8px; border-radius:999px; border:1px solid var(--line); font-size:12px; }
    .tag.ok   { background:#ecfdf5; border-color:#a7f3d0; }     /* green */
    .tag.warn { background:#fffbeb; border-color:#fde68a; }     /* yellow */
    .tag.bad  { background:#fef2f2; border-color:#fecaca; }     /* red */
    </style>
@endsection
    <!-- Action bar for screen -->
  <div class="action-bar">
    <button class="btn" type="button" onclick="window.history.back()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Back
    </button>
    <button class="btn" id="btnPrint" data-action="print" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" /></svg>
      Print
    </button>
  </div>

  <section class="doc" data-entity="jobcard">
    <!-- ===== Header ===== -->
    <header class="header">
      <div class="brand">
        <img class="brand__logo" src="{{asset('images/uploads/'.$company->logo)}}" alt="Company Logo" onerror="this.style.display='none'" />
        <div>
          <div class="brand__name" data-field="company.name">{{$company->name}}</div>
          <div class="brand__meta">
            <span data-field="company.address">{{$company->street_address}} {{$company->suburb}} {{$company->city}} {{$company->country}}</span><br />
            Tel: <span data-field="company.phone">{{$company->phonenumber}}</span>
            · Email: <span data-field="company.email">{{$company->email}}</span>
          </div>
        </div>
      </div>

      <aside class="meta">
        <h1>Workshop Job Card</h1>
        <dl class="meta-grid">
          <dt>Booking #</dt><dd class="mono" data-field="jobcard.number">{{$booking->booking_number}}</dd>
          <dt>JobCard #</dt><dd class="mono" data-field="jobcard.number">{{$ticket->ticket_number}}</dd>
          <dt>Inspection #</dt><dd class="mono" data-field="jobcard.number">{{$inspection->inspection_number}}</dd>
          <dt>Date Opened</dt><dd data-field="jobcard.opened_at">{{$booking->in_date}}</dd>
          <dt>Job Type</dt><dd data-field="jobcard.opened_at">{{$service_type->name}}</dd>
          <dt>Status</dt><dd><span class="tag upper" data-field="jobcard.status">{{$ticket->status == 1 ? "Open" :  "Closed"}}</span></dd>
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== Vehicle & Requester ===== -->
      <section class="grid-2">
      <div class="card">
        <h3>Equipment</h3>
        <div class="kv">
          @if ($ticket->horse)
            <div class="k">Equipment</div><div class="v" data-field="asset.type">Horse</div>
            <div class="k">Reg #</div><div class="v" data-field="asset.registration">{{$ticket->horse->registration_number}}</div>
            <div class="k">Fleet #</div><div class="v" data-field="asset.fleet">{{$ticket->horse->fleet_number}}</div>
            <div class="k">Make / Model</div><div class="v" data-field="asset.make_model">{{$ticket->horse->horse_make ? $ticket->horse->horse_make->name : ""}} {{$ticket->horse->horse_model ? $ticket->horse->horse_model->name : ""}}</div>
            <div class="k">VIN / Chasis</div><div class="v" data-field="asset.make_model">{{$ticket->horse->chasis_number}} </div>
            <div class="k">Engine#</div><div class="v" data-field="asset.make_model">{{$ticket->horse->engine_number}}</div>
          @elseif($ticket->vehicle)
            <div class="k">Equipment</div><div class="v" data-field="asset.type">Vehicle</div>
            <div class="k">Reg #</div><div class="v" data-field="asset.registration">{{$ticket->vehicle->registration_number}}</div>
            <div class="k">Fleet #</div><div class="v" data-field="asset.fleet">{{$ticket->vehicle->fleet_number}}</div>
            <div class="k">Make / Model</div><div class="v" data-field="asset.make_model">{{$ticket->vehicle->vehicle_make ? $ticket->vehicle->vehicle_make->name : ""}} {{$ticket->vehicle->vehicle_model ? $ticket->vehicle->vehicle_model->name : ""}}</div>
            <div class="k">VIN / Chasis</div><div class="v" data-field="asset.make_model">{{$ticket->vehicle->chasis_number}} </div>
            <div class="k">Engine#</div><div class="v" data-field="asset.make_model">{{$ticket->vehicle->engine_number}}</div>
          @elseif($ticket->asset)
            <div class="k">Equipment</div><div class="v" data-field="asset.type">Asset</div>
            <div class="k">Asset</div><div class="v" data-field="asset.registration">{{$ticket->asset->product ? $ticket->asset->product->name : ""}} {{$ticket->asset->product->brand ? $ticket->asset->product->brand->name : ""}}</div>
          @elseif($ticket->trailer)
            <div class="k">Equipment</div><div class="v" data-field="asset.type">Trailer</div>
            <div class="k">Reg #</div><div class="v" data-field="asset.registration">{{$ticket->trailer ? $ticket->trailer->registration_number : ""}}</div>
            <div class="k">Fleet #</div><div class="v" data-field="asset.fleet">{{$ticket->trailer ? $ticket->trailer->fleet_number : ""}}</div>
            <div class="k">Make / Model</div><div class="v" data-field="asset.make_model">{{$ticket->trailer->make}} {{$ticket->trailer->model}}</div>
          @endif
          
          <div class="k">Odometer</div><div class="v" data-field="asset.mileage">{{$booking->odometer}}</div>
          <div class="k">Engine Hours</div><div class="v" data-field="asset.engine_hours">{{$booking->hours}}</div>
          <div class="k">Location</div><div class="v" data-field="asset.location">{{$booking->station}}</div>

        </div>
      </div>

      <div class="card">
        <h3>Requester / Driver</h3>
        <div class="kv">
          <div class="k">Booked By</div><div class="v" data-field="requester.name">{{$booking->user ? $booking->user->name : ""}} {{$booking->user ? $booking->user->surname : ""}}</div>
          <div class="k">Driver</div><div class="v" data-field="driver.name">{{$booking->employee ? $booking->employee->name : ""}} {{$booking->employee ? $booking->employee->surname : ""}}</div>
          <div class="k">Driver Phone</div><div class="v" data-field="driver.phone">{{$booking->employee ? $booking->employee->phonenumber : ""}}</div>
          <div class="k">Reported At</div><div class="v" data-field="reported.at"></div>
          <div class="k">Breakdown Ref</div><div class="v" data-field="breakdown.ref"></div>
        </div>
      </div>

      <div class="card">
        <h3>Reported Faults</h3>
        <div class="notes" data-field="faults.reported">{{$booking->description}}</div>
      </div>

      <div class="card">
        <h3>Initial Diagnosis</h3>
        <div class="notes" data-field="diagnosis.initial">{{$ticket->initial_diagnosis}}</div>
      </div>
    </section>

    <!-- ===== Tasks / Labour ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Labour / Tasks</h3>
      <table class="tbl" role="table" aria-label="Labour Tasks">
        <thead>
          <tr>
            <th style="width:38px" class="center">#</th>
            <th>Task Description</th>
            <th style="width:150px">Assigned To</th>
            <th style="width:120px">Start</th>
            <th style="width:120px">End</th>
            <th style="width:80px" class="right">Hours</th>
            <th style="width:90px" class="right">Rate</th>
            <th style="width:110px" class="right">Amount</th>
            <th style="width:130px">Remarks</th>
          </tr>
        </thead>
        <tbody data-collection="labour">
          <tr data-row>
            <td class="center" data-field="labour.index">1</td>
            <td data-field="labour.description">{{$booking->description}}</td>
            <td data-field="labour.assignee">
                      @if (isset($ticket->booking->employees) && $ticket->booking->employees->count()>0)
                          @foreach ($ticket->booking->employees as $mechanic)
                              {{ $mechanic->name }} {{ $mechanic->surname }}, 
                          @endforeach
                      @elseif(isset($ticket->booking->vendor))
                          {{ucfirst($ticket->booking->vendor->name)}}  
                      @endif
            </td>
            <td data-field="labour.start">{{$booking->in_date}} @ {{$booking->in_time}}</td>
            <td data-field="labour.end">{{$booking->out_date}} {{$booking->out_time ? "@".$booking->out_time : ""}}</td>
            <td class="right" data-field="labour.hours">{{$booking->service_hours}}</td>
            <td class="right" data-field="labour.rate">{{$booking->currency ? $booking->currency->symbol : ""}} {{number_format($booking->rate,2)}}</td>
            <td class="right" data-field="labour.amount">{{$booking->currency ? $booking->currency->symbol : ""}} {{number_format($booking->total,2)}}</td>
            <td data-field="labour.remarks">{{$booking->remarks}}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="right">Labour Total</td>
            <td class="right" data-field="totals.labour_hours">{{$booking->service_hours}}</td>
            <td></td>
            <td class="right" data-field="totals.labour_amount">{{$booking->currency ? $booking->currency->symbol : ""}} {{number_format($booking->total,2)}}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </section>

    <!-- ===== Parts & Materials ===== -->
    @if ($ticket_inventories->count()>0)
        <section class="card" style="margin-top:12px;">
          <h3>Parts & Materials</h3>
          <table class="tbl" role="table" aria-label="Parts and Materials">
            <thead>
              <tr>
                <th style="width:38px" class="center">#</th>
                <th>Part / Material</th>
                <th style="width:130px">Part # / SKU</th>
                <th style="width:80px" class="right">Qty</th>
                <th style="width:90px" class="right">Unit Cost</th>
                <th style="width:110px" class="right">Line Total</th>
                <th style="width:160px">Supplier / Ref</th>
              </tr>
            </thead>
            <tbody data-collection="parts">
              @foreach ($ticket_inventories as $ticket_inventory)
                    <tr data-row>
                        <td class="center" data-field="parts.index">1</td>
                        <td data-field="parts.name">
                            @if ($ticket_inventory->inventory)
                                {{$ticket_inventory->inventory->product ? $ticket_inventory->inventory->product->name : ""}}
                                
                            @elseif ($ticket_inventory->tyre)
                            @php
                                    $tyre = $ticket_inventory->tyre;
                            @endphp
                                {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} 
                            
                            @endif
                        </td>
                        <td class="mono" data-field="parts.sku">
                            @if ($ticket_inventory->inventory)
                                {{$ticket_inventory->inventory->serial_number ? "SN#: ".$ticket_inventory->inventory->serial_number : ""}} {{$ticket_inventory->inventory->part_number ? "PN#: ".$ticket_inventory->inventory->part_number : ""}}
                            @elseif ($ticket_inventory->tyre)
                            @php
                                    $tyre = $ticket_inventory->tyre;
                            @endphp
                                SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} </option>
                            @endif
                        </td>
                        <td class="right" data-field="parts.qty">
                              @if ($ticket_inventory->inventory)
                                {{$ticket_inventory->weight}} {{$ticket_inventory->measurement}}
                              @elseif ($ticket_inventory->tyre)
                                1
                              @endif
                        </td>
                        <td class="right" data-field="parts.unit_cost">
                              @if (isset($ticket_inventory->amount) && is_numeric($ticket_inventory->amount))
                                  {{ $ticket_inventory->currency ? $ticket_inventory->currency->symbol : "" }}{{number_format($ticket_inventory->amount,2)}}        
                              @endif
                        </td>
                        <td class="right" data-field="parts.line_total">
                              @if (isset($ticket_inventory->amount) && is_numeric($ticket_inventory->amount))
                                  {{ $ticket_inventory->currency ? $ticket_inventory->currency->symbol : "" }}{{number_format($ticket_inventory->amount,2)}}        
                              @endif
                        </td>
                        <td data-field="parts.supplier">
                            @if ($ticket_inventory->inventory)
                                {{$ticket_inventory->inventory->vendor ? $ticket_inventory->inventory->vendor->name : ""}} 
                            @elseif ($ticket_inventory->tyre)
                                {{$ticket_inventory->tyre->vendor ? $ticket_inventory->tyre->vendor->name : ""}} 
                            @endif
                        </td>
                    </tr>
              @endforeach
            
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4" class="right">Parts Subtotal</td>
                <td></td>
                <td class="right" data-field="totals.parts_amount">{{number_format($ticket_inventories->sum('amount'),2)}}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </section>
    @endif

    <!-- ===== QA / Road Test ===== -->
    <section  style="margin-top:12px;">
      <div class="card">
        <h3>Technician Notes</h3>
        <div class="notes" data-field="notes.tech">
            {{$ticket->description}}
        </div>
      </div>
    </section>

    <section class="card" style="margin-top:12px;">
        <h3>Inspection Checklist</h3>

        {{-- First preference: show saved results if there are any --}}
        @if(isset($inspection_results) && $inspection_results->isNotEmpty())
          <table class="tbl" role="table" aria-label="Inspection Results">
            <thead>
              <tr>
                <th style="width:38px" class="center">#</th>
                <th>Item</th>
                <th style="width:150px">Status</th>
                <th style="width:120px">Comments</th>
                <th style="width:120px" class="right">Cost</th>
                <th style="width:80px" class="right">Hours</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($inspection_results as $inspection_result)
                @php
                  $status = $inspection_result->status; // 'green' | 'yellow' | 'red'
                  $statusLabel = $status === 'green' ? 'No Attention' : ($status === 'red' ? 'Immediate Attention' : 'Intermediate Attention');
                  $statusClass = $status === 'green' ? 'ok' : ($status === 'red' ? 'bad' : 'warn'); // map to your CSS
                @endphp
                <tr>
                  <td class="center">{{ $loop->iteration }}</td>
                  <td>{{ optional($inspection_result->inspection_type)->name }}</td>
                  <td>
                    <span class="tag {{ $statusClass }}">{{ $statusLabel }}</span>
                  </td>
                  <td>{{ $inspection_result->comments }}</td>
                  <td class="right">{{ number_format((float) $inspection_result->cost, 2) }}</td>
                  <td class="right">{{ number_format((float) $inspection_result->hours, 2) }}</td>
                </tr>
              @empty
                {{-- Shouldn't hit here because of isNotEmpty(), but kept for safety --}}
                <tr><td colspan="6" class="center muted">No inspection results recorded.</td></tr>
              @endforelse
            </tbody>
          </table>

        {{-- Fallback: show the full checklist template if there are no saved results --}}
        @elseif(isset($inspection_services) && $inspection_services->isNotEmpty())
          <table class="tbl" role="table" aria-label="Inspection Checklist Template">
            <thead>
              <tr>
                <th style="width:38px" class="center">#</th>
                <th>Item</th>
                <th style="width:150px">Comments</th>
                <th style="width:80px" class="center">Safe</th>
                <th style="width:80px" class="center">Warning</th>
                <th style="width:80px" class="center">Danger</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($inspection_services as $inspection_service)
                <tr>
                  <td class="center">{{ $loop->iteration }}</td>
                  <td>{{ optional($inspection_service->inspection_type)->name }}</td>
                  <td></td>
                  <td class="center"></td>
                  <td class="center"></td>
                  <td class="center"></td>
                </tr>
              @endforeach
            </tbody>
          </table>

        @else
          <div class="muted">No inspection items configured.</div>
        @endif
    </section>

    <!-- ===== Totals & Approval ===== -->
    <section class="grid-3" style="margin-top:12px;">
      <div class="card">
        <h3>Time Summary</h3>
        <div class="kv">
          <div class="k">Booked Hours</div><div class="v" data-field="summary.booked_hours">{{$booking->booked_hours ? $booking->booked_hours." h" : ""}}</div>
          <div class="k">Actual Hours</div><div class="v" data-field="summary.actual_hours">{{$booking->actual_hours ? $booking->actual_hours." h" : ""}}</div>
          <div class="k">Downtime</div><div class="v" data-field="summary.downtime">{{$booking->downtime_hours ? $booking->downtime_hours." h" : ""}}</div>
        </div>
      </div>
      <div class="card">
        <h3>Cost Summary</h3>
        <div class="kv">
          <div class="k">Labour</div><div class="v" data-field="summary.labour_amount">{{number_format($booking->total,2)}}</div>
          <div class="k">Parts</div><div class="v" data-field="summary.parts_amount">{{number_format($total_parts,2)}}</div>
          <div class="k">Other</div><div class="v" data-field="summary.other_amount">{{number_format($total_expenses,2)}}</div>
          <div class="k">Total</div><div class="v" data-field="summary.total_amount">{{number_format($total,2)}}</div>
        </div>
      </div>
      <div class="card">
        <h3>Completion</h3>
        <div class="kv">
          <div class="k">Closed At</div><div class="v" data-field="jobcard.completed_at">{{$ticket->closed_on}}</div>
          <div class="k">Closed By</div><div class="v" data-field="jobcard.released_by">{{$closed_by ? $closed_by->name : ""}} {{$closed_by ? $closed_by->surname : ""}}</div>
          <div class="k">Next Service (km)</div><div class="v" data-field="jobcard.next_service_km">{{$booking->next_service}}</div>
          <div class="k">Next Service (H)</div><div class="v" data-field="jobcard.next_service_km">{{$booking->next_service_hours}}</div>
          <div class="k">Next Service (date)</div><div class="v" data-field="jobcard.next_service_date">{{$booking->next_service_date}}</div>
        </div>
      </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig"><div class="label">Technician (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Workshop Supervisor (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">QA / Road Test (Name & Sign)</div><div class="line"></div></div>
      <div class="sig label-only"><div class="label">Operations / Customer (Stamp)</div></div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="footer" style="display:flex; justify-content: space-between; align-items:center; margin-top: 14px; font-size: 12px; color: var(--muted);">
      <div>Generated: <span id="printTimestamp" data-field="generated.at"></span></div>
    </footer>
  </section>
</div>