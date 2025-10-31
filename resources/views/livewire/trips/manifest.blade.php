<div>
@section('extra-css')
    <style>
      :root{
      --accent: {{$company->color}};          /* tweak to match your brand */
    }
    </style>
@endsection
    <div class="action-bar">
      <button class="btn" onclick="window.history.back()">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      Back
      </button>
      <button type="button" class="btn" id="btnPrint" data-action="print">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" /></svg>
      Print
      </button>
    </div>

    <section class="manifest" data-entity="manifest">
     
    <!-- ===== Header ===== -->
    <header class="manifest__header">
      <div class="brand">
        <img class="brand__logo" src="{{asset('images/uploads/'.$company->logo)}}" alt="Company Logo" onerror="this.style.display='none'" />
        <div>
          <div class="brand__name" data-field="company.name">{{$company->name}}</div>
          <div class="brand__meta">
            <span data-field="company.address">{{$company->street_address}} {{$company->suburb}} {{$company->city ? ", ".$company->city : ""}} {{$company->country}}</span><br />
            Tel: <span data-field="company.phone">{{$company->phonenumber}}</span>
            · Email: <span data-field="company.email">{{$company->email}}</span>
          </div>
        </div>
      </div>

      <aside class="meta">
        <h1>Transport Manifest</h1>
        <dl class="meta-grid">
          <dt>Manifest #</dt><dd class="mono" data-field="manifest.number">{{$trip->manifest_number}}</dd>
          <dt>Date</dt><dd data-field="manifest.date">{{$trip->start_date}}</dd>
          <dt>Trip #</dt><dd class="mono" data-field="trip.number">{{$trip->trip_number}}</dd>
          <dt>Reference</dt><dd class="mono" data-field="reference">{{$trip->trip_ref}}</dd>
          <dt>Currency</dt><dd data-field="currency">{{$trip->currency ? $trip->currency->name : ""}}</dd>
          <dt>Manifest Created By:</dt><dd data-field="currency">{{$trip->user ? $trip->user->name : ""}} {{$trip->user ? $trip->user->surname : ""}}</dd>
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== Parties & Vehicle ===== -->
    <section class="grid-2">
      <div class="card">
        <h3>Consignor (Shipper)</h3>
        <div class="kv">
          <div class="k">Name</div><div class="v" data-field="shipper.name">{{$customer?->name}}</div>
          <div class="k">Address</div><div class="v" data-field="shipper.address">{{$customer?->street_address}} {{$customer?->suburb ? $customer?->suburb.", " : ""}} {{$customer?->city ? $customer?->city : ""}} {{$customer?->country}}</div>
          <div class="k">Contact</div><div class="v" data-field="shipper.contact">{{$customer?->phonenumber}} {{$customer?->email ? "/".$customer?->email: ""}}</div>
          <div class="k">VAT/TIN</div><div class="v" data-field="shipper.tax_id">{{$customer?->vat_number}} {{$customer?->tin_number ? "/".$customer?->tin_number : ""}}</div>
        </div>
      </div>

      <div class="card">
        <h3>Consignee (Receiver)</h3>
        <div class="kv">
          <div class="k">Name</div><div class="v" data-field="consignee.name">{{$consignee?->name}}</div>
          <div class="k">Address</div><div class="v" data-field="consignee.address">{{$consignee?->street_address}} {{$consignee?->suburb ? $consignee?->suburb.", " : ""}} {{$consignee?->city ? $consignee?->city : ""}} {{$consignee?->country}}</div>
          <div class="k">Contact</div><div class="v" data-field="consignee.contact">{{$consignee?->phonenumber}} {{$consignee?->email}}</div>
          <div class="k">VAT/TIN</div><div class="v" data-field="consignee.tax_id">{{$consignee?->vat_number}} {{$consignee?->tin_number ? "/".$consignee?->tin_number : ""}}</div>
        </div>
      </div>

      <div class="card">
        <h3>Transporter & Vehicle</h3>
        <div class="kv">
          <div class="k">Transporter</div><div class="v" data-field="carrier.name">{{$trip->transporter ? $trip->transporter->name : ""}}</div>
          <div class="k">Horse Reg #</div><div class="v" data-field="vehicle.horse_reg">
            @if ($trip->horse)
                {{$trip->horse ? $trip->horse->registration_number : ""}}
            @elseif($trip->vehicle)
                {{$trip->vehicle ? $trip->vehicle->registration_number : ""}}
            @endif
           </div>
          <div class="k">Trailer(s)</div><div class="v" data-field="vehicle.trailers">
            @foreach ($trip->trailers as $trailer)
                {{$trailer->registration_number}}@if(!$loop->last), @endif 
            @endforeach
          </div>
           @if ($trip->driver)
          <div class="k">Driver</div><div class="v" data-field="driver.name">
               
                    {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
              
          </div>
          <div class="k">License/ID/Passport</div><div class="v" data-field="driver.license">{{$trip->driver->license_number}} {{$trip->driver->employee->idnumber ? " / ".$trip->driver->employee->idnumber : ""}} {{$trip->driver->passport_number ? " / ".$trip->driver->passport_number: ""}} </div>
          <div class="k">Phone</div><div class="v" data-field="driver.phone">{{$trip->driver->employee ? $trip->driver->employee->phonenumber : ""}}</div>
            @endif
        </div>
      </div>

      <div class="card">
        <h3>Route</h3>
        <div class="kv">
          <div class="k">Origin</div><div class="v" data-field="route.origin">{{$from->country ? $from->country->name : ""}} {{$from->city}}</div>
          <div class="k">Loading Point</div><div class="v" data-field="route.origin">{{$trip->loading_point ? $trip->loading_point->name : ""}}</div>
          <div class="k">Destination</div><div class="v" data-field="route.destination">{{$to->country ? $to->country->name : ""}} {{$to->city}}</div>
          <div class="k">Offloading Point</div><div class="v" data-field="route.destination">{{$trip->offloading_point ? $trip->offloading_point->name : ""}}</div>
          <div class="k">Border(s) (if any)</div><div class="v" data-field="route.border">
            @foreach ($trip->borders as $border)
                {{$border->name}}@if(!$loop->last), @endif
            @endforeach
          </div>
        </div>
       
      </div>
    </section>

    <!-- ===== Items ===== -->
    <section class="items">
       <div class="block-keep">
      <h3>Cargo / Items</h3>
      <table class="tbl" role="table" aria-label="Cargo Items">
        <thead>
          <tr>
            <th style="width:40px" class="center">#</th>
            <th>Description</th>
            <th style="width:110px">SKU / Ref</th>
            <th style="width:80px" class="right">Qty</th>
            <th style="width:70px">Unit</th>
            <th style="width:100px" class="right">Weight (Tons)</th>
            <th style="width:100px" class="right">Volume (m³)</th>
          </tr>
        </thead>
        <tbody data-collection="items">
          <!-- Repeat this <tr> for each line item in your app -->
          <tr data-item>
            <td class="center" data-field="item.index">1</td>
            <td data-field="item.description">{{$cargo->name}} {{$trip->cargo_details}}</td>
            <td data-field="item.sku" class="mono">{{$cargo->sku}}</td>
            <td class="right" data-field="item.qty">
                @if ($cargo->type == "Solid")
                    {{$trip->quantity}}
                @elseif($cargo->type == "Liquid")
                    {{$trip->litreage_at_20}}
                @endif
            </td>
            <td data-field="item.unit">{{$trip->measurement}}</td>
            <td class="right" data-field="item.weight">{{$trip->weight}}</td>
            <td class="right" data-field="item.volume">{{$trip->volume}}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="right">Totals</td>
            <td class="right" data-field="totals.qty">
                @if ($cargo->type == "Solid")
                    {{$trip->quantity}}
                @elseif($cargo->type == "Liquid")
                    {{$trip->litreage_at_20}}
                @endif
            </td>
            <td></td>
            <td class="right" data-field="totals.weight">{{$trip->weight}}</td>
            <td class="right" data-field="totals.volume">{{$trip->volume}}</td>
          </tr>
        </tfoot>
      </table>

      <div class="totals">
        <div class="notes" data-field="notes">
         I HEREBY CERTIFY THAT THE GOODS ON THIS MANIFEST ARE A TRUE REFLECTION OF ALL GOODS CARRIED ON THE ABOVE - MENTIONED VEHICLES
        </div>
        <div class="card">
          <div class="kv">
            <div class="k">Gross Weight</div><div class="v" data-field="summary.gross_weight">{{$trip->weight ? $trip->weight."t" : ""}}</div>
            <div class="k">Net Weight</div><div class="v" data-field="summary.net_weight">{{$trip->net_weight ? $trip->net_weight."t" : ""}}</div>
            <div class="k">Total Volume</div><div class="v" data-field="summary.volume">{{$trip->volume ? $trip->volume."m³" : ""}} </div>
            <div class="k">Seal #</div><div class="v" data-field="summary.seal">{{$trip->seal_number}}</div>
            <div class="k">Temp (if cold)</div><div class="v" data-field="summary.temperature">{{$trip->temparature}}</div>
          </div>
        </div>
      </div>
       </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig label-only">
        <div class="label">Custom Stamp - Entry</div>
      </div>
      <div class="sig label-only">
        <div class="label">Custom Stamp - Exit</div>
      </div>
       <div class="sig label-only">
        <div class="label">Report NO(s)</div>
      </div>
      <div class="sig">
        <div class="label">Driver (Name & Sign)</div>
        <div class="line"></div>
        <br>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">Receiver (Name, Sign & Date)</div>
        <div class="line"></div>
        <br>
        <div class="line"></div>
      </div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="manifest__footer">
      <div> <strong>NB:</strong> <span data-field="generated.at">ALL GOODS ARE CARRIED AT OWNER`S RISK</span></div>
    
    </footer>
  </section>

</div>
