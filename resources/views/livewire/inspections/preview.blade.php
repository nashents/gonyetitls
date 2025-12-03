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
        <h1>Job Inspection</h1>
        <dl class="meta-grid">
          <dt>Booking#</dt><dd class="mono" data-field="jobcard.number">{{$booking->booking_number}}</dd>
          <dt>Ticket#</dt><dd class="mono" data-field="jobcard.number">{{$ticket->ticket_number}}</dd>
          <dt>Inspection#</dt><dd class="mono" data-field="jobcard.number">{{$inspection->inspection_number}}</dd>
          <dt>Date Opened</dt><dd data-field="jobcard.opened_at">{{$booking->in_date}}</dd>
          <dt>Job Type</dt><dd data-field="jobcard.opened_at">{{$service_type->name}}</dd>
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <section class="card card--inspection" style="margin-top:12px;">
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
        @endif
    </section>



    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig"><div class="label">Technician (Name & Sign)</div><div class="line"></div></div>
      <div class="sig"><div class="label">Workshop Supervisor (Name & Sign)</div><div class="line"></div></div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="footer" style="display:flex; justify-content: space-between; align-items:center; margin-top: 14px; font-size: 12px; color: var(--muted);">
      <div>Generated: <span id="printTimestamp" data-field="generated.at"></span></div>
    </footer>
  </section>
</div>