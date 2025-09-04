<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title')</title>
   <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
   @yield('extra-css')
  <style>
    /* ---------- Design Tokens ---------- */
    :root{
     
      --ink: #0b0f13;             /* main text */
      --muted: #5b6472;           /* secondary text */
      --line: #e5e7eb;            /* border color */
      --bg: #ffffff;              /* page background */
      --font: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
    }
    

    /* ---------- Page & Layout ---------- */
    html, body { background: var(--bg); color: var(--ink); font-family: var(--font); }
    .manifest { max-width: 1024px; margin: 24px auto; padding: 24px; background: #fff; border: 1px solid var(--line); border-radius: 12px; }
    .manifest h1, .manifest h2, .manifest h3 { margin: 0; }
    .muted { color: var(--muted); }
    .tag { display:inline-block; padding: 2px 8px; border: 1px solid var(--line); border-radius: 999px; font-size: 12px; }
    .hr { height:1px; background: var(--line); margin: 16px 0; }

    /* ---------- Header ---------- */
    .manifest__header { display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; align-items: start; }
    .brand { display:flex; gap: 12px; align-items:flex-start; }
    .brand__logo { width: 96px; height: 96px; object-fit: contain; border-radius: 8px; border:1px solid var(--line); }
    .brand__name { font-size: 20px; font-weight: 700; }
    .brand__meta { font-size: 12px; color: var(--muted); line-height: 1.35; }

    .meta { padding: 12px; border: 1px solid var(--line); border-radius: 10px; }
    .meta h1 { font-size: 20px; letter-spacing: 0.5px; text-transform: uppercase; color: var(--accent); margin-bottom: 8px; }
    .meta-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; font-size: 12px; }
    .meta-grid dt { color: var(--muted); }
    .meta-grid dd { margin: 0; font-weight: 600; }

    /* ---------- Cards / Sections ---------- */
    .grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .card { border: 1px solid var(--line); border-radius: 10px; padding: 12px; }
    .card h3 { font-size: 14px; letter-spacing: .3px; text-transform: uppercase; color: var(--accent); margin-bottom: 8px; }
    .kv { display:grid; grid-template-columns: 140px 1fr; gap: 4px 8px; font-size: 13px; }
    .kv .k { color: var(--muted); }
    .kv .v { font-weight: 600; }

    /* ---------- Waypoints ---------- */
    .waypoints { width:100%; border-collapse: collapse; font-size: 12px; }
    .waypoints th, .waypoints td { border: 1px solid var(--line); padding: 6px 8px; }
    .waypoints th { text-align: left; background: #f8fafc; }

    /* ---------- Items Table ---------- */
    .items { margin-top: 16px; }
    .items h3 { font-size: 14px; letter-spacing: .3px; text-transform: uppercase; color: var(--accent); margin: 0 0 8px 0; }
    table.tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
    .tbl th, .tbl td { border: 1px solid var(--line); padding: 8px; }
    .tbl thead th { background: #f8fafc; font-weight: 700; text-align: left; }
    .tbl td.right, .tbl th.right { text-align: right; }
    .tbl td.center, .tbl th.center { text-align: center; }
    .tbl tfoot td { font-weight: 700; }

    /* ---------- Totals / Notes / Sign-off ---------- */
    .totals { display:grid; grid-template-columns: 1fr 320px; gap: 12px; margin-top: 12px; }
    .notes { border:1px solid var(--line); border-radius: 10px; padding: 10px; min-height: 72px; }

    .signatures { display:grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-top: 12px; }
    .sig { border: 1px dashed var(--line); border-radius: 10px; padding: 10px; min-height: 88px; display:flex; flex-direction: column; justify-content: flex-end; }
    .sig .line { height: 1px; background: var(--ink); margin-top: 26px; }
    .sig .label { font-size: 12px; color: var(--muted); margin-top: 6px; }

    .manifest__footer { display:flex; justify-content: space-between; align-items:center; margin-top: 14px; font-size: 12px; color: var(--muted); }

    /* ---------- Helpers ---------- */
    .flex { display:flex; gap:8px; align-items:center; }
    .spacer { height: 8px; }
    .upper { text-transform: uppercase; letter-spacing: .3px; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

    /* ---------- Print (A4) ---------- */
    @media print {
      body { margin: 0; }
      @page { size: A4; margin: 12mm; }
      .manifest { border: none; border-radius: 0; padding: 0; max-width: none; }
      .tbl thead { display: table-header-group; }
      .tbl tr, .tbl td, .tbl th { page-break-inside: avoid; }
      .signatures { page-break-inside: avoid; }
    }

    @media print {
    @page {
      size: A4 landscape; /* 👈 forces landscape mode */
      margin: 12mm;
    }

    body {
      margin: 0;
    }

    /* optional: keep grids intact in print */
    .grid-2 { grid-template-columns: 1fr 1fr !important; }
    .grid-3 { grid-template-columns: repeat(3, 1fr) !important; }
  }

    /* ---------- Responsive tweaks ---------- */
    @media (max-width: 800px){
      .manifest__header { grid-template-columns: 1fr; }
      .grid-2 { grid-template-columns: 1fr; }
      .signatures { grid-template-columns: 1fr 1fr; }
      .totals { grid-template-columns: 1fr; }
    }

    @media print {
  .brand__logo {
    width: 110px;
    height: 110px;
  }
}

  </style>

  <style>
:root{

--ink: #0b0f13;
--muted: #5b6472;
--line: #e5e7eb;
--bg: #ffffff;
--font: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
}


html, body { background: var(--bg); color: var(--ink); font-family: var(--font); }
.manifest { max-width: 1024px; margin: 24px auto; padding: 24px; background: #fff; border: 1px solid var(--line); border-radius: 12px; }
.manifest h1, .manifest h2, .manifest h3 { margin: 0; }


/* Buttons */
.action-bar { display:flex; justify-content:flex-end; gap:8px; margin-bottom:16px; }
.btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; font-size:14px; font-weight:600; border-radius:8px; border:1px solid var(--line); cursor:pointer; background:#f8fafc; color:var(--ink); transition: all 0.2s ease; }
.btn:hover { background: var(--accent); color:#fff; border-color: var(--accent); }
.btn svg { width:16px; height:16px; }


@media print {
.action-bar { display:none; }
@page { size: A4 landscape; margin: 12mm; }
}

/* --- Keep header and totals side-by-side --- */
.manifest__header { 
  display: grid; 
  grid-template-columns: 1.5fr 1fr; 
  gap: 16px; 
  align-items: start;
}
.totals { 
  display: grid; 
  grid-template-columns: 1fr 320px; 
  gap: 12px; 
  align-items: start;
}

/* --- Print: force landscape and preserve grids --- */
@media print {
  @page { size: A4 landscape; margin: 12mm; }

  /* don't collapse grids in print */
  .manifest__header { grid-template-columns: 1.5fr 1fr !important; }
  .totals { grid-template-columns: 1fr 320px !important; }
  .grid-2 { grid-template-columns: 1fr 1fr !important; }
  .grid-3 { grid-template-columns: repeat(3, 1fr) !important; }

  /* optional: prevent page-break oddities */
  .card, .meta, .waypoints, .tbl { break-inside: avoid; }
}

@media print {
  /* Try to keep all 5 signatures on one row */
  .signatures {
    grid-template-columns: repeat(5, 1fr) !important;
    gap: 6px !important;            /* tighter gaps for print */
  }
  .sig {
    padding: 6px !important;        /* slimmer cards */
    min-height: 68px !important;    /* slightly shorter */
    break-inside: avoid;
    page-break-inside: avoid;
  }
  .sig .label {
    font-size: 10px !important;     /* smaller labels so 5 fit */
  }
  .sig .line {
    margin-top: 18px !important;    /* shorter top spacing */
  }
}

.sig.label-only {
  justify-content: flex-start; /* push content to the top */
}
.sig.label-only .label {
  margin-top: 0; /* remove the spacing */
}

@media print {
  .block-keep { break-inside: avoid-page; page-break-inside: avoid; }
}

@media print {
  .brand__logo {
    width: 110px;
    height: 110px;
  }
}
</style>

</head>
<body>



  @yield('content')
  @yield('extra-js')

  <script>
  (function () {
    function ready(fn){ 
      if (document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
      const btn = document.getElementById('btnPrint') || document.querySelector('.btn[data-action="print"]');
      if (!btn) return;

      const originalTitle = document.title;
      const entity = (document.querySelector('[data-entity]')?.dataset.entity || 'Document')
        .replace(/\b\w/g, c => c.toUpperCase()); // TitleCase

      function timestamp() {
        const d = new Date();
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}`;
      }

      btn.addEventListener('click', function (e) {
        e.preventDefault(); // prevent form submit if inside <form>
        // Suggest a filename like Manifest_2025-09-04_1032
        document.title = `${entity}_${timestamp()}`;
        // tiny delay improves reliability across browsers
        setTimeout(() => window.print(), 50);
      });

      // Restore the original title after printing
      window.addEventListener('afterprint', () => {
        document.title = originalTitle;
      });
    });
  })();
</script>

</body>
</html>