<div>
@section('extra-css')
    <style>
      :root{
        --accent: {{$company->color}};
      }
      .tag { display:inline-block; padding:2px 8px; border-radius:999px; border:1px solid var(--line); font-size:12px; }
      .tag.good      { background:#ecfdf5; border-color:#a7f3d0; }
      .tag.fair      { background:#fffbeb; border-color:#fde68a; }
      .tag.damaged   { background:#fef2f2; border-color:#fecaca; }
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

  <section class="doc" data-entity="grn">

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
        <h1>Goods Received Note</h1>
        <dl class="meta-grid">
          <dt>GRN #</dt>
          <dd class="mono" data-field="grn.number">{{$grn->goods_received_number}}</dd>

          <dt>Delivery Note #</dt>
          <dd class="mono" data-field="grn.delivery_number">{{$grn->delivery_number ?? '—'}}</dd>

          <dt>Date Received</dt>
          <dd data-field="grn.date">
            {{\Carbon\Carbon::parse($grn->date)->format('d M Y')}}
          </dd>

          <dt>Condition</dt>
          <dd>
            @php
              $condClass = match(strtolower($grn->condition ?? '')) {
                'new'    => 'new',
                'second hand' => 'second hand',
                    default   => '',
              };
            @endphp
            @if ($grn->condition)
                <span class="tag {{$condClass}}" data-field="grn.condition">{{ucfirst($grn->condition ?: '')}}</span>
            @endif
            
          </dd>
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== GRN Info ===== -->
    <section class="grid-2">

      <div class="card">
        <h3>Vendor / Supplier</h3>
        <div class="kv">
          <div class="k">Vendor</div>
          <div class="v" data-field="grn.vendor">{{$grn->vendor ? $grn->vendor->name : '—'}}</div>

          <div class="k">Driver Name</div>
          <div class="v" data-field="grn.driver_name">{{$grn->driver_name ?? '—'}}</div>

          <div class="k">Delivery Note #</div>
          <div class="v" data-field="grn.delivery_number">{{$grn->delivery_number ?? '—'}}</div>

          <div class="k">Delivery Date</div>
          <div class="v" data-field="grn.delivery_date">{{\Carbon\Carbon::parse($grn->delivery_date)->format('d M Y')}}</div>
        </div>
      </div>

      <div class="card">
        <h3>Receiving Details</h3>
        <div class="kv">
          <div class="k">Received By</div>
          <div class="v" data-field="grn.employee">
            {{$grn->employee ? $grn->employee->name : '—'}} {{$grn->employee ? $grn->employee->surname : ''}}
          </div>

          <div class="k">Date Received</div>
          <div class="v" data-field="grn.date">
            {{\Carbon\Carbon::parse($grn->date)->format('d M Y')}}
          </div>

          <div class="k">Total Items</div>
          <div class="v" data-field="grn.total_items">{{$grn->total_items}}</div>

          <div class="k">Total Value</div>
          <div class="v" data-field="grn.total">{{number_format($grn->total, 2)}}</div>
        </div>
      </div>

    </section>

    <!-- ===== Received Items ===== -->
    <section class="card" style="margin-top:12px;">
      <h3>Received Items</h3>
      <table class="tbl" role="table" aria-label="Received Items">
        <thead>
          <tr>
            <th class="center" style="width:4%">#</th>
            <th style="width:130px">Item</th>
            <th style="width:80px">ID#</th>
            <th style="width:80px" class="center">Type</th>
            <th style="width:80px" class="right">Qty</th>
            <th style="width:80px" class="right">Unit Cost</th>
            <th style="width:80px" class="right">Amount</th>
          </tr>
        </thead>
        <tbody data-collection="grn-items">

          @php $i = 1; @endphp

          {{-- Inventories --}}
          @foreach ($grn->inventories as $inventory)
            <tr data-row>
              <td class="center">{{$i++}}</td>
              <td data-field="item.product">
                {{optional($inventory->product)->name}}
                @if(optional($inventory->product->brand)->name)
                  <span class="muted">· {{$inventory->product->brand->name}}</span>
                @endif
              </td>
              <td class="mono" data-field="item.serial">
                {{$inventory->serial_number ? 'SN#: '.$inventory->serial_number : ''}}
                {{$inventory->product?->identification_number   ? 'PN#: '.$inventory->product?->identification_number   : ''}}
              </td>
              <td class="center"><span class="tag">Inventory</span></td>
              <td class="right">{{$inventory->qty ?? 1}} {{$inventory->units_of_measure?->name}}</td>
              <td class="right">{{number_format($inventory->amount ?? 0, 2)}}</td>
              <td class="right">{{number_format($inventory->subtotal_incl ?? 0, 2)}}</td>
            </tr>
          @endforeach

          {{-- Tyres --}}
          @foreach ($grn->tyres as $tyre)
            <tr data-row>
              <td class="center">{{$i++}}</td>
              <td data-field="item.product">
                {{optional($tyre->product->brand)->name}} {{optional($tyre->product)->name}}
              </td>
              <td class="mono" data-field="item.serial">
                SN#: {{$tyre->serial_number}} · {{$tyre->width}}/{{$tyre->aspect_ratio}} R{{$tyre->diameter}}
              </td>
              <td class="center"><span class="tag">Tyre</span></td>
              <td class="right">1</td>
              <td class="right">{{number_format($tyre->amount ?? 0, 2)}}</td>
              <td class="right">{{number_format($tyre->subtotal_incl ?? 0, 2)}}</td>
            </tr>
          @endforeach

          {{-- Assets --}}
          @foreach ($grn->assets as $asset)
          <tr data-row>
              <td class="center">{{$i++}}</td>
              <td data-field="item.product">
                {{optional($asset->product)->name}}
                @if(optional($asset->product->brand)->name)
                  <span class="muted">· {{$asset->product->brand->name}}</span>
                @endif
              </td>
              <td class="mono" data-field="item.serial">
                {{$asset->serial_number ? 'SN#: '.$asset->serial_number : ''}}
                {{$asset->product->identification_number   ? 'PN#: '.$asset->product?->identification_number   : ''}}
              </td>
              <td class="center"><span class="tag">Asset</span></td>
              <td class="right">{{$asset->qty ?? 1}} {{$asset->units_of_measure?->name}}</td>
              <td class="right">{{number_format($asset->amount ?? 0, 2)}}</td>
              <td class="right">{{number_format($asset->subtotal_incl ?? 0, 2)}}</td>
            </tr>
          @endforeach

          @if($i === 1)
            <tr><td colspan="7" class="center muted">No items on this GRN.</td></tr>
          @endif

        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="right">Total Items</td>
            <td class="right" data-field="grn.total_items">{{$grn->total_items}}</td>
            <td class="right"><strong>Total</strong></td>
            <td class="right" data-field="grn.total"><strong>{{number_format($grn->total, 2)}}</strong></td>
          </tr>
        </tfoot>
      </table>
    </section>

    <!-- ===== Condition & Comments ===== -->
    <section class="grid-2" style="margin-top:12px;">
      <div class="card">
        <h3>Condition</h3>
        <div class="kv">
          <div class="k">Overall Condition</div>
          <div class="v">
            @if ($grn->condition)
                <span class="tag {{$condClass}}">{{ucfirst($grn->condition ?? '')}}</span>
            @endif
            </div>
        </div>
      </div>

      <div class="card">
        <h3>Comments / Remarks</h3>
        <div class="notes" data-field="grn.comments">{{$grn->comments ?? ''}}</div>
      </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig"><div class="label">Received By (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Store / Warehouse (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Supplier / Driver (Name & Sign)</div><div class="line"></div></div>
      <div class="sig label-only"><div class="label">Management (Stamp)</div></div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:12px; color:var(--muted);">
      <div>Generated: <span id="printTimestamp" data-field="generated.at"></span></div>
    </footer>

  </section>
</div>
