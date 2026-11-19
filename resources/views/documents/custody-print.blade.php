@extends('layouts.manifest')

@section('title')
    Custody Log - {{ $document->title }}
@endsection

@php
    // The record the document is filed under, e.g. "Customer: Total Zimbabwe"
    $category = (string) $document->category;
    $linked = null;
    if ($category && $category !== 'all' && method_exists($document, $category)) {
        $rec = $document->{$category};
        if ($rec) {
            $linked = (isset($rec->name) && isset($rec->surname)) ? trim($rec->name . ' ' . $rec->surname)
                : ($rec->name ?? $rec->registration_number ?? $rec->trip_number ?? $rec->title ?? ('#' . $rec->id));
        }
    }
    $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y H:i') : '';
    $statusLabel = ['pending' => 'In transit', 'received' => 'Received', 'rejected' => 'Rejected'];
    $statusColor = ['pending' => '#d97706', 'received' => '#1e8e3e', 'rejected' => '#c0392b'];
    $address = collect([$company->street_address ?? null, $company->suburb ?? null, $company->city ?? null, optional($company->country ?? null)->name ?? (is_string($company->country ?? null) ? $company->country : null)])
        ->map(fn ($p) => trim((string) $p, " ,"))->filter()->implode(', ');
    $received = $movements->where('status', 'received')->count();
@endphp

@section('extra-css')
<style>
  :root { --accent: {{ $company->color ?: '#123bdc' }}; }

  .status-pill { display:inline-block; padding:1px 8px; border-radius:999px; font-size:11px; font-weight:700; color:#fff; white-space:nowrap; }
  .tbl td { vertical-align: top; }
  .tbl .who { font-weight: 700; }
  .tbl .small { font-size: 11px; color: var(--muted); }
  .sig-cell { margin-top: 6px; height: 34px; border-bottom: 1px solid var(--ink); display:flex; align-items:flex-end; }
  .sig-cell img { max-height: 32px; max-width: 100%; }
  .sig-cell.blank { border-bottom: 1px dashed var(--muted); }
  .sig-caption { font-size: 10px; color: var(--muted); margin-top: 2px; }
  .versions { margin: 0; padding-left: 18px; font-size: 12px; }
  .versions li { margin: 2px 0; }
  #custodyQr img, #custodyQr canvas { width: 90px !important; height: 90px !important; }

  @media print {
    .tbl .small, .versions { font-size: 9px !important; }
    .sig-cell { height: 26px !important; margin-top: 3px !important; }
    .sig-cell img { max-height: 24px !important; }
    .sig-caption { font-size: 8px !important; }
    .status-pill { font-size: 8px !important; padding: 0 5px !important; }
    #custodyQr img, #custodyQr canvas { width: 70px !important; height: 70px !important; }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
@endsection

@section('content')
<div class="action-bar" style="max-width:1024px;margin:16px auto 0;">
  <a class="btn" href="{{ route('documents.custody', $document->id) }}" style="text-decoration:none;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
    Back
  </a>
  <button type="button" class="btn" id="btnPrint" data-action="print">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z" /></svg>
    Print
  </button>
</div>

<section class="manifest" data-entity="custody log">

  {{-- ===== Header (same as manifest / trip sheet) ===== --}}
  <header class="manifest__header">
    <div class="brand">
      <img class="brand__logo" src="{{ asset('images/uploads/' . $company->logo) }}" alt="Company Logo" onerror="this.style.display='none'" />
      <div>
        <div class="brand__name">{{ $company->name }}</div>
        <div class="brand__meta">
          {{ $address }}<br />
          @if($company->phonenumber)Tel: {{ $company->phonenumber }}<br />@endif
          @if($company->email){{ $company->email }}@endif
        </div>
      </div>
    </div>

    <aside class="meta">
      <div style="float:right; margin-left:8px;" id="custodyQr" data-url="{{ route('documents.custody', ['document' => $document->id, 'action' => 'scan']) }}"></div>
      <h1>Document Custody Log</h1>
      <dl class="meta-grid">
        <dt>Document #</dt>
        <dd class="mono">{{ $document->id }}</dd>
        <dt>Hand-overs</dt>
        <dd>{{ $movements->count() }} ({{ $received }} received)</dd>
        <dt>Printed</dt>
        <dd>{{ now()->format('d M Y H:i') }}</dd>
        <dt>Printed By</dt>
        <dd>{{ $printedBy }}</dd>
      </dl>
    </aside>
  </header>

  <div class="hr"></div>

  {{-- ===== Document & current status ===== --}}
  <section class="grid-2">
    <div class="card">
      <h3>Document</h3>
      <div class="kv">
        <div class="k">Title</div>
        <div class="v">{{ $document->title }}</div>
        <div class="k">Filed under</div>
        <div class="v">{{ ucwords(str_replace('_', ' ', $category)) }}@if($linked): {{ $linked }}@endif</div>
        <div class="k">Expiry</div>
        <div class="v">{{ $document->expires_at ? \Carbon\Carbon::parse($document->expires_at)->format('d M Y') : 'No expiry' }}</div>
        <div class="k">Uploaded</div>
        <div class="v">{{ $fmt($document->created_at) }}@if($document->user) by {{ trim($document->user->name . ' ' . $document->user->surname) }}@endif</div>
      </div>
    </div>

    <div class="card">
      <h3>Current Custody</h3>
      <div class="kv">
        <div class="k">Currently with</div>
        <div class="v">{{ $holder['name'] }}</div>
        <div class="k">Location</div>
        <div class="v">{{ $holder['location'] ?: '—' }}</div>
        <div class="k">Status</div>
        <div class="v">
          @if($holder['in_transit'])
            <span class="status-pill" style="background:{{ $statusColor['pending'] }}">In transit</span>
            to {{ $holder['to'] }}@if(!empty($holder['to_location'])), {{ $holder['to_location'] }}@endif
          @else
            <span class="status-pill" style="background:{{ $statusColor['received'] }}">Received</span>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- ===== Hand-overs (the receipt book) ===== --}}
  <section class="items">
    <h3>Hand-overs</h3>
    <table class="tbl">
      <thead>
        <tr>
          <th class="center" style="width:4%">#</th>
          <th style="width:30%">Handed over by</th>
          <th style="width:30%">Received by</th>
          <th style="width:24%">Location / purpose</th>
          <th class="center" style="width:12%">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $i => $m)
          <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>
              <div class="who">{{ $m->fromLabel() }}</div>
              <div>{{ $fmt($m->handed_over_at) }}</div>
              <div class="small">Condition: {{ $m->handover_condition ?: 'N/A' }}</div>
              @if($m->handover_notes)<div class="small">{{ $m->handover_notes }}</div>@endif
              @if($m->handover_file)<div class="small">Scan on file</div>@endif
              <div class="sig-cell {{ $m->handover_signature ? '' : 'blank' }}">@if($m->handover_signature)<img src="{{ $m->handover_signature }}" alt="signature">@endif</div>
              <div class="sig-caption">Signature</div>
            </td>
            <td>
              <div class="who">{{ $m->toLabel() }}</div>
              @if($m->received_at)
                <div>{{ $fmt($m->received_at) }}</div>
                @if($m->status === 'received')<div class="small">Condition: {{ $m->receipt_condition ?: 'N/A' }}</div>@endif
                @if($m->receipt_notes)<div class="small">{{ $m->status === 'rejected' ? 'Reason: ' : '' }}{{ $m->receipt_notes }}</div>@endif
                @if($m->receiver && $m->received_by != $m->to_user_id)<div class="small">Captured by {{ trim($m->receiver->name . ' ' . $m->receiver->surname) }}</div>@endif
                @if($m->receipt_file)<div class="small">Scan on file</div>@endif
              @else
                <div class="small">Not yet received</div>
              @endif
              <div class="sig-cell {{ $m->receipt_signature ? '' : 'blank' }}">@if($m->receipt_signature)<img src="{{ $m->receipt_signature }}" alt="signature">@endif</div>
              <div class="sig-caption">Signature</div>
            </td>
            <td>
              {{ $m->location ?: '—' }}
              @if($m->purpose)<div class="small">{{ $m->purpose }}</div>@endif
            </td>
            <td class="center">
              <span class="status-pill" style="background:{{ $statusColor[$m->status] ?? '#6b7480' }}">{{ $statusLabel[$m->status] ?? $m->status }}</span>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="center muted">No hand-overs yet. The document is with {{ $holder['name'] }}.</td></tr>
        @endforelse
      </tbody>
    </table>
  </section>

  {{-- ===== Versions + sign-off ===== --}}
  <section class="totals">
    <div class="notes">
      <strong class="upper" style="color:var(--accent);font-size:12px;">Versions</strong>
      @if(count($versions))
        <ol class="versions">
          @foreach($versions as $v)
            <li>{{ $v['label'] }} <span class="muted">{{ $v['at'] ? \Carbon\Carbon::parse($v['at'])->format('d M Y H:i') : '' }}</span>@if($loop->last && $v['file'] === $document->filename) <span class="tag">Current</span>@endif</li>
          @endforeach
        </ol>
      @else
        <div class="muted">No file on record.</div>
      @endif
    </div>
    <div class="signatures" style="grid-template-columns:1fr; margin-top:0;">
      <div class="sig">
        <div class="line"></div>
        <div class="label">Verified by (name, signature &amp; date)</div>
      </div>
    </div>
  </section>

  <footer class="manifest__footer">
    <span>{{ $company->name }} · Document custody log · Doc #{{ $document->id }}</span>
    <span>Powered by Gonyeti TLS</span>
  </footer>
</section>
@endsection

@section('extra-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  (function () {
    var el = document.getElementById('custodyQr');
    if (el && window.QRCode) {
      new QRCode(el, { text: el.getAttribute('data-url'), width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
    }
  })();
</script>
@endsection
