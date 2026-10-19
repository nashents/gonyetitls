{{--
    MODERN invoice styles — shared by the screen/print page and the PDF.
    dompdf-safe: tables instead of flex/grid, solid hex colours instead of rgba.
    Expects $company (accent colour).
--}}
@php $accent = $company->color ?: '#185FA5'; @endphp
<style>
    .inv-wrap {
        font-family: 'Roboto', 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        width: 210mm;
        margin: 0 auto;
        padding: 0 0 14mm;
        box-sizing: border-box;
    }
    .inv-wrap table { border-collapse: collapse; }
    .inv-layout { width: 100%; }
    .inv-layout > tbody > tr > td, .inv-layout > tr > td { vertical-align: top; padding: 0; }

    /* ── DARK HEADER ── */
    .inv-header { width: 100%; background: #1a1a1a; color: #fff; }
    .inv-header td { padding: 22px 28px; vertical-align: middle; }
    .inv-logo-wrap { margin-bottom: 10px; }
    .inv-logo-wrap img { max-height: 48px; max-width: 160px; filter: brightness(0) invert(1); }
    .inv-coname { font-size: 16px; font-weight: 700; letter-spacing: 0.5px; color: #fff; margin-bottom: 5px; }
    .inv-codetail { font-size: 9.5px; color: #bcbcbc; line-height: 1.75; }
    .inv-codetail a { color: #d1d1d1; text-decoration: none; }
    .inv-header-right { text-align: right; }
    .inv-doc-type { font-size: 24px; font-weight: 700; letter-spacing: 1.5px; color: #fff; margin-bottom: 4px; }
    .inv-number { font-size: 13px; color: #d1d1d1; margin-bottom: 12px; letter-spacing: 0.3px; }
    .inv-pill-row { text-align: right; margin-top: 4px; }
    .inv-pill {
        display: inline-block;
        background: #313131;
        border: 1px solid #484848;
        border-radius: 999px;
        padding: 2px 12px;
        font-size: 9.5px;
        color: #fff;
        white-space: nowrap;
    }
    .inv-pill span { color: #949494; margin-right: 5px; }

    /* ── ACCENT STRIPE ── */
    .inv-accent { height: 4px; background: {{ $accent }}; font-size: 0; line-height: 0; }

    /* ── BODY ── */
    .inv-body { padding: 20px 28px 14px; }

    .inv-billing-row { width: 100%; margin-bottom: 18px; }
    .inv-billing-row > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
    .inv-billing-row td.inv-gap { width: 16px; }
    .inv-billing-box { border: 0.5px solid #ddd; border-radius: 6px; }
    .inv-billing-box-hdr {
        background: #f4f4f4;
        padding: 5px 10px;
        font-size: 8.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #555;
        border-bottom: 0.5px solid #ddd;
    }
    .inv-billing-box-body { padding: 9px 10px; font-size: 9.5px; line-height: 1.8; color: #333; }
    .inv-billing-box-body .cname { font-size: 11px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
    .inv-kv { width: 100%; }
    .inv-kv td { padding: 0 5px 0 0; vertical-align: top; line-height: 1.8; }
    .inv-kv td.lbl { color: #888; width: 72px; white-space: nowrap; }
    .inv-kv .mono { font-family: monospace; font-weight: 700; }
    .inv-qr { border: 1px solid #ddd; border-radius: 3px; }
    .inv-qr-note { font-size: 8.5px; color: #888; word-break: break-all; }

    /* ── ITEMS TABLE ── */
    .inv-items { width: 100%; margin-bottom: 16px; font-size: 10px; }
    .inv-items thead tr { background: #1a1a1a; color: #fff; }
    .inv-items th { padding: 7px 8px; text-align: right; font-weight: bold; letter-spacing: 0.2px; background: #1a1a1a; color: #fff; }
    .inv-items th.left { text-align: left; }
    .inv-items tbody tr.even td { background: #f9f9f9; }
    .inv-items td { padding: 7px 8px; text-align: right; border-bottom: 0.5px solid #ececec; vertical-align: middle; }
    .inv-items td.left { text-align: left; }
    .inv-items tfoot td { padding: 5px 8px; text-align: right; border: none; }
    .inv-items tfoot tr.subtotal td { border-top: 0.5px solid #ddd; }
    .inv-items tfoot tr.grand td { background: #1a1a1a; color: #fff; font-weight: 700; font-size: 11px; }

    /* ── BOTTOM (notes + banking) ── */
    .inv-bottom { width: 100%; margin-top: 10px; font-size: 10px; }
    .inv-bottom > tbody > tr > td { width: 50%; vertical-align: top; padding: 0 16px 0 0; }
    .inv-section-label { font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #999; margin-bottom: 5px; }
    .inv-notes { color: #444; line-height: 1.7; }
    .inv-bank { color: #333; line-height: 1.85; margin-bottom: 6px; }

    /* ── FOOTER ── */
    .inv-doc-footer { width: 100%; margin-top: 20px; border-top: 0.5px solid #ddd; font-size: 9px; color: #aaa; }
    .inv-doc-footer td { padding-top: 10px; vertical-align: middle; }
    .inv-footer-brand { text-align: right; white-space: nowrap; }
    .inv-footer-brand .dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: {{ $accent }};
        border-radius: 50%;
        margin-right: 4px;
    }

    @media print {
        .inv-wrap { width: 100%; padding: 0; }
        .inv-body { padding: 16px 20px 10px; }
        .inv-header td { padding: 18px 20px; }
    }
</style>
