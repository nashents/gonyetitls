<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Custody Label - {{ $document->title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef1f5; font-family: Arial, Helvetica, sans-serif; color: #111; }
        .toolbar { max-width: 420px; margin: 18px auto 0; display: flex; gap: 8px; justify-content: space-between; align-items: center; padding: 0 16px; }
        .toolbar button, .toolbar a { font-size: 14px; padding: 8px 14px; border-radius: 4px; border: 1px solid #b8c0c8; background: #fff; color: #111; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #123bdc; border-color: #123bdc; color: #fff; }
        .label { width: 100mm; max-width: calc(100% - 32px); margin: 14px auto 24px; background: #fff; border: 1px dashed #888; padding: 6mm; text-align: center; }
        .company { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: #555; }
        .title { font-size: 17px; font-weight: bold; margin: 4px 0 2px; word-break: break-word; }
        .meta { font-size: 11px; color: #555; margin-bottom: 4mm; }
        #qr { display: inline-block; padding: 2mm; background: #fff; }
        #qr img, #qr canvas { width: 46mm !important; height: 46mm !important; }
        .instr { font-size: 12px; margin-top: 3mm; line-height: 1.4; }
        .url { font-size: 9px; color: #666; margin-top: 2mm; word-break: break-all; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .label { margin: 0; border: 1px dashed #888; }
            @page { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('documents.custody', $document->id) }}">&larr; Back</a>
        <button type="button" onclick="window.print()">Print label</button>
    </div>

    <div class="label">
        <div class="company">{{ $company->name ?? config('app.name') }}</div>
        <div class="title">{{ $document->title }}</div>
        <div class="meta">
            Doc #{{ $document->id }} · {{ ucwords(str_replace('_', ' ', $document->category)) }}
            @if($document->expires_at) · Exp {{ \Carbon\Carbon::parse($document->expires_at)->format('d M Y') }} @endif
        </div>
        <div id="qr" data-url="{{ $scanUrl }}"></div>
        <div class="instr">
            <strong>Scan to sign this document in or out.</strong><br>
            Receiving it? Scan, log in and confirm receipt.<br>
            Passing it on? Scan and record the hand-over.
        </div>
        <div class="url">{{ $scanUrl }}</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            var el = document.getElementById('qr');
            if (window.QRCode) {
                new QRCode(el, { text: el.getAttribute('data-url'), width: 320, height: 320, correctLevel: QRCode.CorrectLevel.M });
            } else {
                el.textContent = 'QR code could not load (offline?). Use the link below.';
            }
        })();
    </script>
</body>
</html>
