<div>
@section('extra-css')
    <style>
      :root{
        --accent: {{$company->color}};
      }
      .tag { display:inline-block; padding:2px 8px; border-radius:999px; border:1px solid var(--line); font-size:12px; }
      .tag.pending  { background:#fffbeb; border-color:#fde68a; }
      .tag.authorized { background:#ecfdf5; border-color:#a7f3d0; }
      .tag.rejected { background:#fef2f2; border-color:#fecaca; }
    </style>
@endsection

  <!-- Action bar -->
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

  <section class="doc" data-entity="dispatch">

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
        <h1>Dispatch Note</h1>
        <dl class="meta-grid">
          <dt>Dispatch #</dt>
          <dd class="mono" data-field="dispatch.number">{{$dispatch->dispatch_number}}</dd>

          <dt>Linked Job Card</dt>
          <dd class="mono" data-field="dispatch.ticket">
            {{$dispatch->ticket ? $dispatch->ticket->ticket_number : '—'}}
          </dd>

          <dt>Date</dt>
          <dd data-field="dispatch.date">{{$dispatch->created_at ? $dispatch->created_at->format('d M Y') : ''}}</dd>

          <dt>Status</dt>
          <dd>
            @php
              $authClass = match($dispatch->authorization) {
                'authorized' => 'authorized',
                'rejected'   => 'rejected',
                default      => 'pending',
              };
              $authLabel = ucfirst($dispatch->authorization ?? 'Pending');
            @endphp
            <span class="tag {{$authClass}}" data-field="dispatch.status">{{$authLabel}}</span>
          </dd>
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== Dispatch Info & Equipment ===== -->
    <section class="grid-2">

      <div class="card">
        <h3>Dispatch Details</h3>
        <div class="kv">
          <div class="k">Requested By</div>
          <div class="v" data-field="dispatch.requested_by">
            {{$dispatch->requestedBy ? $dispatch->requestedBy->name : ''}} {{$dispatch->requestedBy ? $dispatch->requestedBy->surname : ''}}
          </div>

          <div class="k">Issued To (Employee)</div>
          <div class="v" data-field="dispatch.employee">
            {{$dispatch->employee ? $dispatch->employee->name : ''}} {{$dispatch->employee ? $dispatch->employee->surname : ''}}
          </div>

          <div class="k">Department</div>
          <div class="v" data-field="dispatch.department">
            @php
                $dpt = App\Models\Department::find($dispatch->department_id);
            @endphp
            {{$dpt?->name}}
          </div>

          <div class="k">Branch</div>
          <div class="v" data-field="dispatch.branch">
            {{$dispatch->branch ? $dispatch->branch->name : '—'}}
          </div>

          <div class="k">Currency</div>
          <div class="v" data-field="dispatch.currency">
            {{$dispatch->currency ? $dispatch->currency->name : '—'}}
          </div>
        </div>
      </div>

      <div class="card">
        <h3>Equipment</h3>
        <div class="kv">
          @if ($dispatch->horse)
            <div class="k">Type</div><div class="v">Horse</div>
            <div class="k">Reg (Fleet) #</div>
            <div class="v">{{$dispatch->horse->registration_number}} {{$dispatch->horse->fleet_number ? "(".$dispatch->horse->fleet_number.")" : ""}}</div>
            <div class="k">Make / Model</div>
            <div class="v">{{optional($dispatch->horse->horse_make)->name}} {{optional($dispatch->horse->horse_model)->name}}</div>
          @elseif ($dispatch->vehicle)
            <div class="k">Type</div><div class="v">Vehicle</div>
            <div class="k">Reg (Fleet) #</div>
            <div class="v">{{$dispatch->vehicle->registration_number}} {{$dispatch->vehicle->fleet_number ? "(".$dispatch->vehicle->fleet_number.")" : ""}}</div>
            <div class="k">Make / Model</div>
            <div class="v">{{optional($dispatch->vehicle->vehicle_make)->name}} {{optional($dispatch->vehicle->vehicle_model)->name}}</div>
          @else
            <div class="k">Equipment</div><div class="v muted">None linked</div>
          @endif
        </div>
      </div>

    </section>

    <!-- ===== Dispatch Items ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Dispatched Items</h3>
      <table class="tbl" role="table" aria-label="Dispatch Items">
        <thead>
          <tr>
            <th class="center" style="width:4%">#</th>
            <th>Product / Part</th>
            <th style="width:80px" class="right">Qty</th>
            <th style="width:110px" class="right">Unit Cost</th>
            <th style="width:120px" class="right">Amount</th>
          </tr>
        </thead>
        <tbody data-collection="dispatch-items">
          @forelse ($dispatch->dispatch_items as $item)
            <tr data-row>
              <td class="center" data-field="item.index">{{$loop->iteration}}</td>
              <td data-field="item.product">
                {{$item->product ? $item->product->name : '—'}}
              </td>
              <td class="right" data-field="item.qty">{{$item->qty}}</td>
              <td class="right" data-field="item.unit_cost">
                {{$dispatch->currency ? $dispatch->currency->symbol : ''}} {{number_format($item->unit_cost, 2)}}
              </td>
              <td class="right" data-field="item.amount">
                {{$dispatch->currency ? $dispatch->currency->symbol : ''}} {{number_format($item->amount, 2)}}
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="center muted">No items on this dispatch.</td></tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="right"><strong>Total</strong></td>
            <td class="right" data-field="dispatch.total">
              <strong>{{$dispatch->currency ? $dispatch->currency->symbol : ''}} {{number_format($dispatch->total, 2)}}</strong>
            </td>
          </tr>
        </tfoot>
      </table>
    </section>

    <!-- ===== Authorization ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Authorization</h3>
      <div class="kv">
        <div class="k">Status</div>
        <div class="v"><span class="tag {{$authClass}}">{{$authLabel}}</span></div>

        <div class="k">Authorized By</div>
        <div class="v" data-field="auth.authorized_by">
          {{$dispatch->authorizedBy ? $dispatch->authorizedBy->name : '—'}} {{$dispatch->authorizedBy ? $dispatch->authorizedBy->surname : ''}}
        </div>

        <div class="k">Authorization Date</div>
        <div class="v" data-field="auth.date">
          {{$dispatch->authorization_date ? \Carbon\Carbon::parse($dispatch->authorization_date)->format('d M Y') : '—'}}
        </div>

        <div class="k">Comments</div>
        <div class="v" data-field="auth.comments">{{$dispatch->authorization_comments ?? '—'}}</div>
      </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig"><div class="label">Issued By (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Received By (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Authorized By (Name & Sign)</div><div class="line"></div></div>
      <div class="sig label-only"><div class="label">Store / Warehouse (Stamp)</div></div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:12px; color:var(--muted);">
      <div>Generated: <span id="printTimestamp" data-field="generated.at"></span></div>
    </footer>

  </section>
</div>
