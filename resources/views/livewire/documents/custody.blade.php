<div class="container-fluid custody-page">
    <style>
        .custody-page .cust-card { background:#fff; border:1px solid #e3e7ec; border-radius:6px; padding:16px; margin-bottom:16px; }
        .custody-page .cust-head { display:flex; flex-wrap:wrap; justify-content:space-between; gap:12px; align-items:flex-start; }
        .custody-page .cust-holder { font-size:18px; font-weight:600; }
        .custody-page .cust-label { color:#6b7480; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .custody-page .cust-pending { border-left:4px solid #f0ad4e; }
        .custody-page .sig-wrap canvas { width:100%; height:130px; border:1px dashed #b8c0c8; border-radius:4px; background:#fafbfc; touch-action:none; display:block; }
        .custody-page .cust-log th, .custody-page .cust-log td { vertical-align:top; font-size:13px; }
        .custody-page .cust-log img.sig { max-height:40px; max-width:120px; display:block; }
        .custody-page .cust-versions li { padding:6px 0; border-bottom:1px solid #f0f2f4; }
        .custody-page .cust-qr-box { text-align:center; }
        .custody-page #custodyQr img, .custody-page #custodyQr canvas { width:96px !important; height:96px !important; margin:0 auto; }
        .custody-page .cust-focus { box-shadow:0 0 0 3px rgba(18,59,220,.35); transition:box-shadow .4s; }
        @media print {
            .no-print, .sidebar, .navbar, .breadcrumb-div, .page-title-div { display:none !important; }
            .custody-page .cust-card { border:none; padding:0; }
        }
    </style>

    {{-- Summary --}}
    <div class="cust-card">
        <div class="cust-head">
            <div>
                <div class="cust-label">Document</div>
                <h4 style="margin:2px 0 4px">
                    <a href="{{ asset('myfiles/documents/'.$document->filename) }}" target="_blank">{{ $document->title }}</a>
                </h4>
                <small class="text-muted">
                    {{ ucwords(str_replace('_', ' ', $document->category)) }}
                    @if($document->expires_at) · Expires {{ \Carbon\Carbon::parse($document->expires_at)->format('d M Y') }} @endif
                </small>
            </div>
            <div>
                <div class="cust-label">Currently with</div>
                <div class="cust-holder">{{ $holder['name'] }}</div>
                @if($holder['location'])<small class="text-muted"><i class="fa fa-map-marker"></i> {{ $holder['location'] }}</small><br>@endif
                @if($holder['in_transit'])
                    <span class="badge bg-warning">In transit to {{ $holder['to'] }}@if(!empty($holder['to_location'])) ({{ $holder['to_location'] }})@endif — awaiting receipt</span>
                @else
                    <span class="badge bg-success">Received</span>
                @endif
            </div>
            <div class="cust-qr-box">
                <div wire:ignore id="custodyQr" data-url="{{ route('documents.custody', ['document' => $document->id, 'action' => 'scan']) }}"></div>
                <small class="text-muted d-block">Scan to sign in / out</small>
            </div>
            <div class="no-print">
                <a class="btn btn-default" href="{{ route('documents.custody.print', $document->id) }}" target="_blank"><i class="fa fa-print"></i> Print custody log</a>
                <a class="btn btn-default" href="{{ route('documents.custody.label', $document->id) }}" target="_blank"><i class="fa fa-qrcode"></i> Print QR label</a>
            </div>
        </div>
    </div>

    @php
        $focusTarget = null;
        if ($focus === 'receive' || ($focus === 'scan' && $canReceive)) {
            $focusTarget = $canReceive ? 'cust-receive' : null;
        } elseif ($focus === 'handover' || ($focus === 'scan' && $canHandOver)) {
            $focusTarget = $canHandOver ? 'cust-handover' : null;
        }
    @endphp
    @if($focus === 'receive' && !$canReceive)
        <div class="alert alert-info no-print">
            <i class="fa fa-info-circle"></i>
            @if($pending)
                This hand-over is addressed to {{ $pending->toLabel() }}, so only they (or an administrator) can confirm receipt.
            @else
                There is nothing waiting for receipt on this document — it may already have been received, rejected or cancelled. The log below shows what happened.
            @endif
        </div>
    @endif

    {{-- Pending hand-over: receive / reject / cancel --}}
    @if($pending)
        <div class="cust-card cust-pending no-print" id="cust-receive">
            <h5 style="margin-top:0"><i class="fa fa-hourglass-half"></i>
                Handed over by <strong>{{ $pending->fromLabel() }}</strong> to <strong>{{ $pending->toLabel() }}</strong>
                on {{ optional($pending->handed_over_at)->format('d M Y H:i') }}
            </h5>
            <p class="text-muted" style="margin-bottom:10px">
                Condition: {{ $pending->handover_condition ?: 'N/A' }}
                @if($pending->location) · Location: {{ $pending->location }} @endif
                @if($pending->purpose) · Purpose: {{ $pending->purpose }} @endif
                @if($pending->handover_notes)<br>{{ $pending->handover_notes }}@endif
            </p>

            @if($receiveOnBehalf && !$show_on_behalf)
                {{-- Admin who is not the recipient: hidden behind a deliberate step --}}
                <p><i class="fa fa-info-circle"></i> Waiting for {{ $pending->toLabel() }} to confirm receipt.</p>
                <a href="#" wire:click.prevent="$set('show_on_behalf', true)"><small><i class="fa fa-user-secret"></i> Confirm or reject on {{ $pending->toLabel() }}'s behalf (admin)</small></a>
            @elseif($canReceive)
                @if(!$pending->to_user_id)
                    <div class="alert alert-info" style="padding:8px 12px">
                        External recipient — capture their receipt on their behalf (e.g. upload the signed slip or have them sign below).
                    </div>
                @elseif($receiveOnBehalf)
                    <div class="alert alert-warning" style="padding:8px 12px">
                        You are recording this on <strong>{{ $pending->toLabel() }}</strong>'s behalf. The log will show it was captured by you.
                        <a href="#" wire:click.prevent="$set('show_on_behalf', false)">Cancel</a>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Condition on receipt <span style="color:red">*</span></label>
                            <select class="form-control" wire:model.defer="receipt_condition">
                                @foreach($conditions as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                            </select>
                            @error('receipt_condition') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Received at <span style="color:red">*</span></label>
                            <input type="datetime-local" class="form-control" wire:model.defer="received_at">
                            @error('received_at') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Scan of document as received <small class="text-muted">(new stamps / endorsements — becomes the latest version)</small></label>
                            <input type="file" class="form-control" wire:model="receipt_scan" id="rs{{ $rand }}" accept=".pdf,image/*">
                            <div wire:loading wire:target="receipt_scan"><small>Uploading…</small></div>
                            @error('receipt_scan') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" rows="2" wire:model.defer="receipt_notes" placeholder="Anything missing, damaged, stamped…"></textarea>
                </div>
                <div class="form-group sig-wrap" wire:ignore>
                    <label>Signature <small class="text-muted">(optional — sign with mouse or finger)</small></label>
                    <canvas data-custody-pad></canvas>
                    <a href="#" onclick="custodyPad.clear(this);return false;"><small>Clear signature</small></a>
                </div>
                <button type="button" class="btn bg-success btn-rounded" wire:loading.attr="disabled"
                        onclick="custodyPad.submit(this, 'receive', {{ $pending->id }})">
                    <i class="fa fa-check"></i> Confirm receipt
                </button>

                <div class="row" style="margin-top:14px">
                    <div class="col-md-8">
                        <input type="text" class="form-control" wire:model.defer="reject_reason" placeholder="Reason, if you are rejecting this hand-over">
                        @error('reject_reason') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-danger btn-rounded" wire:click="reject({{ $pending->id }})"
                                onclick="return confirm('Reject this hand-over? The document stays with the sender.')">
                            <i class="fa fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            @else
                <p><i class="fa fa-info-circle"></i> Waiting for {{ $pending->toLabel() }} to confirm receipt.
                    @if($isSender) It is waiting on their dashboard, and they were emailed a link to sign for it if they have an email address. @endif
                </p>
            @endif

            @if($canCancel)
                <button type="button" class="btn btn-link text-danger" style="padding-left:0" wire:click="cancel({{ $pending->id }})"
                        onclick="return confirm('Cancel this hand-over?')">
                    <i class="fa fa-undo"></i> Cancel hand-over
                </button>
            @endif
        </div>
    @endif

    {{-- New hand-over --}}
    @if($canHandOver)
        <div class="cust-card no-print" id="cust-handover">
            <h5 style="margin-top:0"><i class="fa fa-share"></i> Hand over to next person</h5>
            @error('cancel') <div class="text-danger">{{ $message }}</div> @enderror
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Recipient is</label>
                        <select class="form-control" wire:model="recipient_type">
                            <option value="user">A system user</option>
                            <option value="external">External person / office</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    @if($recipient_type === 'user')
                        <div class="form-group">
                            <label>Recipient <span style="color:red">*</span></label>
                            <select class="form-control" wire:model.defer="to_user_id">
                                <option value="">Select user</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ trim($u->name.' '.$u->surname) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">They will confirm receipt from their dashboard.</small>
                            @error('to_user_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    @else
                        <div class="form-group">
                            <label>Recipient name / office <span style="color:red">*</span></label>
                            <input type="text" class="form-control" wire:model.defer="to_name" placeholder="e.g. J. Moyo, Ministry of Transport">
                            @error('to_name') <div class="text-danger">{{ $message }}</div> @enderror
                            @error('to_user_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Location / desk</label>
                        <input type="text" class="form-control" wire:model.defer="location" placeholder="e.g. Beitbridge border office">
                    </div>
                </div>
            </div>
            <div class="row">
                @if(!$holder['user_id'])
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Handed over by</label>
                            <input type="text" class="form-control" wire:model.defer="from_name" placeholder="{{ $holder['name'] }}">
                        </div>
                    </div>
                @endif
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Condition <span style="color:red">*</span></label>
                        <select class="form-control" wire:model.defer="handover_condition">
                            @foreach($conditions as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Handed over at <span style="color:red">*</span></label>
                        <input type="datetime-local" class="form-control" wire:model.defer="handed_over_at">
                        @error('handed_over_at') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Purpose</label>
                        <input type="text" class="form-control" wire:model.defer="purpose" placeholder="e.g. For endorsement">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Scan at hand-over <small class="text-muted">(optional)</small></label>
                        <input type="file" class="form-control" wire:model="handover_scan" id="hs{{ $rand }}" accept=".pdf,image/*">
                        <div wire:loading wire:target="handover_scan"><small>Uploading…</small></div>
                        @error('handover_scan') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" rows="2" wire:model.defer="handover_notes"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group sig-wrap" wire:ignore>
                <label>Signature <small class="text-muted">(optional)</small></label>
                <canvas data-custody-pad></canvas>
                <a href="#" onclick="custodyPad.clear(this);return false;"><small>Clear signature</small></a>
            </div>
            <button type="button" class="btn bg-success btn-rounded" wire:loading.attr="disabled"
                    onclick="custodyPad.submit(this, 'handOver')">
                <i class="fa fa-share"></i> Record hand-over
            </button>
        </div>
    @elseif(!$pending)
        <div class="cust-card no-print text-muted">
            <i class="fa fa-lock"></i> Only {{ $holder['name'] }} (the current holder) or an administrator can hand this document over.
        </div>
    @endif

    {{-- Receipt book --}}
    <div class="cust-card">
        <h5 style="margin-top:0"><i class="fa fa-book"></i> Custody log</h5>
        <div class="table-responsive">
            <table class="table table-bordered cust-log">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Handed over</th>
                        <th>Received</th>
                        <th>Location / purpose</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $i => $m)
                        <tr wire:key="mv-{{ $m->id }}">
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <strong>{{ $m->fromLabel() }}</strong><br>
                                {{ optional($m->handed_over_at)->format('d M Y H:i') }}<br>
                                Condition: {{ $m->handover_condition ?: 'N/A' }}
                                @if($m->handover_notes)<br><small>{{ $m->handover_notes }}</small>@endif
                                @if($m->handover_file)<br><a href="{{ asset('myfiles/documents/'.$m->handover_file) }}" target="_blank"><i class="fa fa-paperclip"></i> Scan</a>@endif
                                @if($m->handover_signature)<img class="sig" src="{{ $m->handover_signature }}" alt="signature">@endif
                            </td>
                            <td>
                                <strong>{{ $m->toLabel() }}</strong><br>
                                @if($m->received_at)
                                    {{ $m->received_at->format('d M Y H:i') }}<br>
                                    @if($m->status === 'received')Condition: {{ $m->receipt_condition ?: 'N/A' }}@endif
                                    @if($m->receipt_notes)<br><small>{{ $m->receipt_notes }}</small>@endif
                                    @if($m->receiver && $m->received_by != $m->to_user_id)<br><small class="text-muted">Captured by {{ trim($m->receiver->name.' '.$m->receiver->surname) }}</small>@endif
                                    @if($m->receipt_file)<br><a href="{{ asset('myfiles/documents/'.$m->receipt_file) }}" target="_blank"><i class="fa fa-paperclip"></i> Scan</a>@endif
                                    @if($m->receipt_signature)<img class="sig" src="{{ $m->receipt_signature }}" alt="signature">@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $m->location }}@if($m->purpose)<br><small>{{ $m->purpose }}</small>@endif</td>
                            <td>
                                @if($m->status === 'received')<span class="badge bg-success">Received</span>
                                @elseif($m->status === 'rejected')<span class="badge bg-danger">Rejected</span>
                                @else<span class="badge bg-warning">In transit</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No hand-overs yet. The document is with {{ $holder['name'] }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Versions --}}
    <div class="cust-card">
        <h5 style="margin-top:0"><i class="fa fa-files-o"></i> Versions ({{ count($versions) }})</h5>
        <ul class="list-unstyled cust-versions" style="margin:0">
            @foreach($versions as $n => $v)
                <li>
                    <strong>v{{ $n + 1 }}</strong> ·
                    <a href="{{ asset('myfiles/documents/'.$v['file']) }}" target="_blank">{{ $v['label'] }}</a>
                    <small class="text-muted">{{ $v['at'] ? \Carbon\Carbon::parse($v['at'])->format('d M Y H:i') : '' }}</small>
                    @if($loop->last && $v['file'] === $document->filename)<span class="badge bg-info">Current</span>@endif
                </li>
            @endforeach
        </ul>
    </div>

    <script>
        (function () {
            // QR (rendered in the browser - the URL is never sent to a third party)
            function drawQr() {
                var el = document.getElementById('custodyQr');
                if (!el || el.childNodes.length || !window.QRCode) return;
                new QRCode(el, { text: el.getAttribute('data-url'), width: 192, height: 192, correctLevel: QRCode.CorrectLevel.M });
            }
            // Jump to the action the email / QR link was opened for
            function focusAction() {
                var target = @json($focusTarget);
                var el = target && document.getElementById(target);
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.classList.add('cust-focus');
                setTimeout(function () { el.classList.remove('cust-focus'); }, 2500);
            }
            function init() { drawQr(); focusAction(); }
            if (document.readyState === 'complete') { init(); } else { window.addEventListener('load', init); }
        })();

        window.custodyPad = window.custodyPad || (function () {
            var state = new WeakMap();

            function setup(canvas) {
                var s = state.get(canvas);
                if (s && canvas.width === Math.round(canvas.offsetWidth * (window.devicePixelRatio || 1))) return s;
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                var ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#0b0f13';
                s = { ctx: ctx, drawing: false, dirty: false };
                state.set(canvas, s);
                return s;
            }
            function pt(canvas, e) { var r = canvas.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; }

            document.addEventListener('pointerdown', function (e) {
                var c = e.target.closest && e.target.closest('canvas[data-custody-pad]');
                if (!c) return;
                e.preventDefault();
                var s = setup(c); c.setPointerCapture(e.pointerId);
                var p = pt(c, e); s.drawing = true; s.dirty = true;
                s.ctx.beginPath(); s.ctx.moveTo(p.x, p.y); s.ctx.lineTo(p.x + .1, p.y + .1); s.ctx.stroke();
            });
            document.addEventListener('pointermove', function (e) {
                var c = e.target.closest && e.target.closest('canvas[data-custody-pad]');
                var s = c && state.get(c);
                if (!s || !s.drawing) return;
                e.preventDefault(); var p = pt(c, e); s.ctx.lineTo(p.x, p.y); s.ctx.stroke();
            });
            ['pointerup', 'pointercancel'].forEach(function (n) {
                document.addEventListener(n, function (e) {
                    var c = e.target.closest && e.target.closest('canvas[data-custody-pad]');
                    var s = c && state.get(c); if (s) s.drawing = false;
                });
            });

            function padIn(el) { var card = el.closest('.cust-card'); return card && card.querySelector('canvas[data-custody-pad]'); }
            function clearCanvas(c) { if (!c) return; var s = state.get(c); if (s) { s.ctx.clearRect(0, 0, c.width, c.height); s.dirty = false; } }

            window.addEventListener('custody-saved', function () {
                document.querySelectorAll('canvas[data-custody-pad]').forEach(clearCanvas);
            });

            return {
                clear: function (el) { clearCanvas(padIn(el)); },
                submit: function (btn, method, id) {
                    var c = padIn(btn), s = c && state.get(c);
                    var sig = s && s.dirty ? c.toDataURL('image/png') : null;
                    var comp = Livewire.find(btn.closest('[wire\\:id]').getAttribute('wire:id'));
                    if (method === 'receive') comp.call('receive', id, sig); else comp.call('handOver', sig);
                }
            };
        })();
    </script>
</div>
