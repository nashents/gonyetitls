<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title')</title>
  @yield('extra-css')
  <style>
  :root{
--accent: #0f766e;
--ink: #0b0f13;
--muted: #5b6472;
--line: #9ca3af;
--bg: #ffffff;
--font: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
}


html, body { background: var(--bg); color: var(--ink); font-family: var(--font); }
.doc { max-width: 1024px; margin: 24px auto; padding: 24px; background: #fff; border: 1px solid var(--line); border-radius: 12px; }
.hr { height:1px; background: var(--line); margin: 16px 0; }


.action-bar { display:flex; justify-content:flex-end; gap:8px; margin: 16px auto -8px; max-width: 1024px; }
.btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; font-size:14px; font-weight:600; border-radius:8px; border:1px solid var(--line); cursor:pointer; background:#f8fafc; color:var(--ink); transition: all 0.2s ease; }
.btn:hover { background: var(--accent); color:#fff; border-color: var(--accent); }
.btn svg { width:16px; height:16px; }


.header { display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; align-items: start; }
.brand { display:flex; gap: 12px; align-items:flex-start; }
.brand__logo { width: 96px; height: 96px; object-fit: contain; border-radius: 8px; border:1px solid var(--line); }
.brand__name { font-size: 20px; font-weight: 700; }
.brand__meta { font-size: 12px; color: var(--muted); line-height: 1.35; }


.meta { padding: 12px; border: 1px solid var(--line); border-radius: 10px; }
.meta h1 { font-size: 20px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--accent); margin-bottom: 8px; }
.meta-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; font-size: 12px; }
.meta-grid dt { color: var(--muted); }
.meta-grid dd { margin: 0; font-weight: 600; }


.grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.grid-3 { display:grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.card { border: 1px solid var(--line); border-radius: 10px; padding: 12px; }
.card h3 { font-size: 14px; letter-spacing: .3px; text-transform: uppercase; color: var(--accent); margin-bottom: 8px; }
.kv { display:grid; grid-template-columns: 140px 1fr; gap: 4px 8px; font-size: 13px; }


table.tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.tbl th, .tbl td { border: 0.8pt solid var(--line); padding: 8px; }
.tbl thead th { background: #eef2f7; font-weight: 700; text-align: left; }
.tbl td.right, .tbl th.right { text-align: right; }
.tbl td.center, .tbl th.center { text-align: center; }
.tbl tfoot td { font-weight: 700; }


.notes { border:1px solid var(--line); border-radius: 10px; padding: 10px; min-height: 72px; }
.qa-list { list-style: none; padding-left: 0; margin: 0; display:grid; grid-template-columns: repeat(2, 1fr); gap: 8px 16px; font-size: 12px; }
.qa-item { display:flex; justify-content: space-between; gap: 8px; border-bottom: 1px dashed var(--line); padding-bottom: 6px; }


.signatures { display:grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 12px; }
.sig { border: 1px dashed var(--line); border-radius: 10px; padding: 10px; min-height: 88px; display:flex; flex-direction: column; justify-content: flex-end; }
.sig .line { height: 1px; background: var(--ink); margin-top: 26px; }
.sig .label { font-size: 12px; color: var(--muted); margin-top: 6px; }
.sig.label-only { justify-content: flex-start; }


@media print {
@page { size: A4 portrait; margin: 12mm; }
body { margin: 0; }
.action-bar { display:none; }
.doc { border: none; border-radius: 0; padding: 0; max-width: none; }


* { -webkit-print-color-adjust: exact; print-color-adjust: exact; }


.header { grid-template-columns: 1fr 1fr !important; }
.grid-2 { grid-template-columns: 1fr 1fr !important; }
.grid-3 { grid-template-columns: 1fr !important; }
.signatures { grid-template-columns: 1fr 1fr !important; gap: 8px !important; }


.brand__logo { width: 80px; height: 80px; }


.card, .meta, .tbl { break-inside: avoid; page-break-inside: avoid; }
.tbl tr { break-inside: avoid; page-break-inside: avoid; }

.card--inspection { break-inside: auto; page-break-inside: auto; }
  .card--inspection .tbl { break-inside: auto; page-break-inside: auto; }
  .card--inspection > h3 { break-after: avoid-page; }

}

@media print {
  /* Keep separate borders & zero spacing (you already have this) */
  .tbl {
    border-collapse: separate !important;
    border-spacing: 0 !important;

    /* Paint a guaranteed right edge on every printed fragment */
    box-shadow: inset -1pt 0 0 #9ca3af !important;
    border-right: 1pt solid #9ca3af !important; /* belt & braces */
    table-layout: fixed !important;             /* more stable pagination */
  }

  /* Make sure header repeats and rows don't split */
  .tbl thead { display: table-header-group; }
  .tbl tr    { break-inside: avoid; page-break-inside: avoid; }

  /* Last column keeps its own right border too */
  .tbl th:last-child,
  .tbl td:last-child {
    border-right: 1pt solid #9ca3af !important;
  }

  /* Avoid clipping */
  .card { overflow: visible !important; }
}




@media (max-width: 800px){
.header { grid-template-columns: 1fr; }
.grid-2 { grid-template-columns: 1fr; }
.grid-3 { grid-template-columns: 1fr; }
.signatures { grid-template-columns: 1fr 1fr; }
}

  </style>
</head>
<body>

  @yield('content')

  <!-- Print title + restore logic -->
  <script>
    (function () {
      function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
      ready(function () {
        const btn = document.getElementById('btnPrint') || document.querySelector('.btn[data-action="print"]');
        if (!btn) return;
        const originalTitle = document.title;
        const entity = (document.querySelector('[data-entity]')?.dataset.entity || 'Document').replace(/\b\w/g, c => c.toUpperCase());
        function timestamp(){ const d = new Date(); const pad=n=>String(n).padStart(2,'0'); return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}`; }
        btn.addEventListener('click', function(e){
          e.preventDefault();
          document.title = `${entity}_${timestamp()}`;
          setTimeout(() => window.print(), 50);
        });
        window.addEventListener('afterprint', () => { document.title = originalTitle; });
      });
    })();
  </script>

<script>
(function () {
  function pad(n) { return String(n).padStart(2, '0'); }

  function getTimestamp() {
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} `
         + `${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('btnPrint');
    const ts = document.getElementById('printTimestamp');
    const originalTitle = document.title;
    const entity = (document.querySelector('[data-entity]')?.dataset.entity || 'Document')
      .replace(/\b\w/g, c => c.toUpperCase());

    btn?.addEventListener('click', e => {
      e.preventDefault();
      // set timestamp
      if (ts) ts.textContent = getTimestamp();

      // set filename prefix
      document.title = `${entity}_${getTimestamp().replace(/[: ]/g, '_')}`;

      setTimeout(() => window.print(), 50);
    });

    window.addEventListener('afterprint', () => {
      document.title = originalTitle;
    });
  });
})();
</script>

  @yield('extra-js')
</body>
</html>
