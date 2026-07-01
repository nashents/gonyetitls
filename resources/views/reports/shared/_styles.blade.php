{{-- Wave-style print/PDF layout, shared by every financial report. --}}
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #333333;
        font-size: 13px;
        margin: 30px 40px;
    }
    .ps-title {
        margin: 0;
        font-size: 28px;
        font-weight: 400;
        color: #333333;
    }
    .ps-company {
        font-size: 16px;
        font-weight: bold;
        margin-top: 4px;
    }
    .ps-meta {
        color: #888888;
        margin-top: 8px;
    }
    .ps-logo {
        max-width: 140px;
        max-height: 70px;
    }
    table.ps-table {
        width: 100%;
        border-collapse: collapse;
    }
    .ps-columns-head td {
        font-weight: bold;
        padding-bottom: 10px;
        vertical-align: bottom;
    }
    .ps-rule {
        border: none;
        border-top: 2px solid #333333;
        margin: 0 0 14px 0;
    }
    .ps-section-head td {
        background-color: #e4e7e9;
        font-weight: bold;
        padding: 8px 10px;
    }
    .ps-row td {
        padding: 7px 10px;
    }
    .ps-total-row td {
        padding: 10px;
        font-weight: bold;
        border-top: 1px solid #cccccc;
        border-bottom: 2px solid #333333;
    }
    .ps-metric td {
        background-color: #e4e7e9;
        padding: 10px;
        font-weight: bold;
    }
    .ps-metric .ps-subtext {
        font-weight: normal;
        color: #888888;
        font-size: 12px;
        margin-top: 2px;
    }
    .ps-right {
        text-align: right;
    }
    .ps-footer {
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #cccccc;
        color: #888888;
        font-size: 11px;
    }
    .ps-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 3px;
        font-weight: bold;
        font-size: 12px;
    }
    .ps-badge-ok { background-color: #dff0d8; color: #3c763d; }
    .ps-badge-bad { background-color: #f2dede; color: #a94442; }
</style>
