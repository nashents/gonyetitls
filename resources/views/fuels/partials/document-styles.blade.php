{{-- Fuel order documents on the trip-document layout (layouts.jobcard). --}}
<style>
  :root { --accent: {{ $company->color ?: '#0f766e' }}; }
  .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
  /* Order numbers stay in the body font: in monospace the letter O in "FPFO00261" reads as a zero. */
  .order-no { font-size: 22px; font-weight: 800; letter-spacing: 1px; color: var(--accent); }
  .order-ref { font-size: 15px; letter-spacing: .5px; }
  .notice { margin-top: 12px; padding: 10px 12px; border: 1px solid var(--accent); border-left: 6px solid var(--accent); border-radius: 10px; font-size: 13px; }
  .card p { margin: 0; font-size: 13px; line-height: 1.45; }
  .tbl td.order { font-weight: 800; font-size: 13px; letter-spacing: .5px; white-space: nowrap; }
  .tbl td.sign { min-width: 120px; }
  .doc + .doc { margin-top: 32px; }
  .footer { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; font-size: 12px; color: var(--muted); }
  @media print {
    .doc { page-break-after: always; break-after: page; }
    .doc:last-of-type { page-break-after: auto; break-after: auto; }
    .doc + .doc { margin-top: 0; }
  }
</style>
