<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Transport Manifest</title>
  <style>
    /* ---------- Design Tokens ---------- */
    :root{
      --accent: #0f766e;          /* tweak to match your brand */
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
    .brand__logo { width: 64px; height: 64px; object-fit: contain; border-radius: 8px; border:1px solid var(--line); }
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

    /* ---------- Responsive tweaks ---------- */
    @media (max-width: 800px){
      .manifest__header { grid-template-columns: 1fr; }
      .grid-2 { grid-template-columns: 1fr; }
      .signatures { grid-template-columns: 1fr 1fr; }
      .totals { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <section class="manifest" data-entity="manifest">
    <!-- ===== Header ===== -->
    <header class="manifest__header">
      <div class="brand">
        <img class="brand__logo" src="logo.png" alt="Company Logo" onerror="this.style.display='none'" />
        <div>
          <div class="brand__name" data-field="company.name">Your Company (Pvt) Ltd</div>
          <div class="brand__meta">
            <span data-field="company.address">123 Seke Rd, Graniteside, Harare</span><br />
            Tel: <span data-field="company.phone">+263 (0) 77 000 0000</span>
            · Email: <span data-field="company.email">ops@yourcompany.co.zw</span>
          </div>
        </div>
      </div>

      <aside class="meta">
        <h1>Transport Manifest</h1>
        <dl class="meta-grid">
          <dt>Manifest #</dt><dd class="mono" data-field="manifest.number">MAN-000123</dd>
          <dt>Date</dt><dd data-field="manifest.date">2025-09-01</dd>
          <dt>Trip #</dt><dd class="mono" data-field="trip.number">TRP-04567</dd>
          <dt>Reference</dt><dd class="mono" data-field="reference">PO-90210</dd>
          <dt>Currency</dt><dd data-field="currency">USD</dd>
          <dt>Status</dt><dd><span class="tag upper" data-field="status">Dispatched</span></dd>
        </dl>
      </aside>
    </header>

    <div class="hr"></div>

    <!-- ===== Parties & Vehicle ===== -->
    <section class="grid-2">
      <div class="card">
        <h3>Consignor (Shipper)</h3>
        <div class="kv">
          <div class="k">Name</div><div class="v" data-field="shipper.name">Acme Mines Ltd</div>
          <div class="k">Address</div><div class="v" data-field="shipper.address">12 Borrowdale Rd, Harare</div>
          <div class="k">Contact</div><div class="v" data-field="shipper.contact">+263 71 234 5678 · John</div>
          <div class="k">VAT/TIN</div><div class="v" data-field="shipper.tax_id">12345678</div>
        </div>
      </div>

      <div class="card">
        <h3>Consignee (Receiver)</h3>
        <div class="kv">
          <div class="k">Name</div><div class="v" data-field="consignee.name">Beta Steel Ltd</div>
          <div class="k">Address</div><div class="v" data-field="consignee.address">Plot 44, Msasa, Harare</div>
          <div class="k">Contact</div><div class="v" data-field="consignee.contact">+263 78 111 2222 · Tariro</div>
          <div class="k">VAT/TIN</div><div class="v" data-field="consignee.tax_id">99887766</div>
        </div>
      </div>

      <div class="card">
        <h3>Carrier & Vehicle</h3>
        <div class="kv">
          <div class="k">Carrier</div><div class="v" data-field="carrier.name">Your Company Logistics</div>
          <div class="k">Horse Reg #</div><div class="v" data-field="vehicle.horse_reg">AFB 1234</div>
          <div class="k">Trailer(s)</div><div class="v" data-field="vehicle.trailers">ABC 5678 · ADE 9012</div>
          <div class="k">Driver</div><div class="v" data-field="driver.name">T. Moyo</div>
          <div class="k">License/ID</div><div class="v" data-field="driver.license">LIC 556677 / 29-123456-A-12</div>
          <div class="k">Phone</div><div class="v" data-field="driver.phone">+263 77 333 4444</div>
        </div>
      </div>

      <div class="card">
        <h3>Route</h3>
        <div class="kv">
          <div class="k">Origin</div><div class="v" data-field="route.origin">Harare</div>
          <div class="k">Destination</div><div class="v" data-field="route.destination">Bulawayo</div>
          <div class="k">Border (if any)</div><div class="v" data-field="route.border">—</div>
        </div>
        <div class="spacer"></div>
        <table class="waypoints">
          <thead>
            <tr><th style="width:40px">#</th><th>Stop / Checkpoint</th><th style="width:160px">Date & Time</th><th style="width:180px">Stamp / Signature</th></tr>
          </thead>
          <tbody>
            <tr><td class="center">1</td><td>Dispatch Yard</td><td></td><td></td></tr>
            <tr><td class="center">2</td><td>Toll/Weighbridge</td><td></td><td></td></tr>
            <tr><td class="center">3</td><td>Customer Gate</td><td></td><td></td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===== Items ===== -->
    <section class="items">
      <h3>Cargo / Items</h3>
      <table class="tbl" role="table" aria-label="Cargo Items">
        <thead>
          <tr>
            <th style="width:40px" class="center">#</th>
            <th>Description</th>
            <th style="width:110px">SKU / Ref</th>
            <th style="width:80px" class="right">Qty</th>
            <th style="width:70px">Unit</th>
            <th style="width:100px" class="right">Weight (kg)</th>
            <th style="width:100px" class="right">Volume (m³)</th>
            <th style="width:160px">Remarks</th>
          </tr>
        </thead>
        <tbody data-collection="items">
          <!-- Repeat this <tr> for each line item in your app -->
          <tr data-item>
            <td class="center" data-field="item.index">1</td>
            <td data-field="item.description">Steel Rods — 12m</td>
            <td data-field="item.sku" class="mono">SR-12M</td>
            <td class="right" data-field="item.qty">20</td>
            <td data-field="item.unit">Bundles</td>
            <td class="right" data-field="item.weight">4,000.00</td>
            <td class="right" data-field="item.volume">6.20</td>
            <td data-field="item.remarks">Stack evenly</td>
          </tr>
          <tr data-item>
            <td class="center" data-field="item.index">2</td>
            <td data-field="item.description">Binding Wire</td>
            <td data-field="item.sku" class="mono">BW-10</td>
            <td class="right" data-field="item.qty">50</td>
            <td data-field="item.unit">Coils</td>
            <td class="right" data-field="item.weight">1,200.00</td>
            <td class="right" data-field="item.volume">1.10</td>
            <td data-field="item.remarks"></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="right">Totals</td>
            <td class="right" data-field="totals.qty">70</td>
            <td></td>
            <td class="right" data-field="totals.weight">5,200.00</td>
            <td class="right" data-field="totals.volume">7.30</td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      <div class="totals">
        <div class="notes" data-field="notes">
          Handle with care. All cargo transported at owner’s risk unless otherwise stated. Ensure load is secured and tarpaulins fastened.
        </div>
        <div class="card">
          <div class="kv">
            <div class="k">Gross Weight</div><div class="v" data-field="summary.gross_weight">5,200.00 kg</div>
            <div class="k">Net Weight</div><div class="v" data-field="summary.net_weight">—</div>
            <div class="k">Total Volume</div><div class="v" data-field="summary.volume">7.30 m³</div>
            <div class="k">Seal #</div><div class="v" data-field="summary.seal">SE-000987</div>
            <div class="k">Temp (if cold)</div><div class="v" data-field="summary.temperature">—</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== Sign-off ===== -->
    <section class="signatures">
      <div class="sig">
        <div class="label">Prepared By (Name & Sign)</div>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">Driver (Name & Sign)</div>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">Dispatch (Name & Sign)</div>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">Security (Stamp & Sign)</div>
        <div class="line"></div>
      </div>
      <div class="sig">
        <div class="label">Receiver (Name, Sign & Date)</div>
        <div class="line"></div>
      </div>
    </section>

    <!-- ===== Footer ===== -->
    <footer class="manifest__footer">
      <div>Generated: <span data-field="generated.at">2025-09-01 10:00</span></div>
      <div class="upper">Page <span class="mono">1</span></div>
    </footer>
  </section>
</body>
</html>
