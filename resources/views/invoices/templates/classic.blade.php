@extends('layouts.main')
@section('extra-css')
<link rel="shortcut icon" type="image/png" href="{{ asset('images/uploads/company-logo.png') }}">
@endsection
@section('title')
Invoice Preview | Gonyeti Transport Ltd
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                 <div id="print-area">
                    <div id="invoice">
                        <x-loading/>
                        <div class="toolbar hidden-print">
                            <div class="text-end">
                                <button type="button" onclick="goBack()"
                                        class="btn btn-default border-primary btn-wide btn-rounded">
                                    <i class="fa fa-arrow-left" style="color:black"></i> Back
                                </button>

                                {{-- fiscalStatus = 'approved' --}}
                                <span class="btn btn-success btn-wide btn-rounded" style="cursor:default">
                                    <i class="fa fa-check-circle"></i> Fiscalised
                                </span>

                                {{-- fiscalStatus = 'approved' && fiscalPdfFile exists --}}
                                <button type="button" class="btn btn-success btn-wide btn-rounded">
                                    <i class="fa fa-file-pdf-o"></i> Download Fiscal PDF
                                </button>

                                <a href="javascript:void(0)" onclick="printSection()"
                                    class="btn btn-default border-primary btn-wide btn-rounded">
                                    <i class="fa fa-print" style="color: black"></i> Print
                                </a>

                                <a href="#"
                                    class="btn btn-default border-primary btn-wide btn-rounded">
                                    <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
                                </a>
                            </div>

                            {{-- STATUS BANNER --}}
                            <div class="alert mt-2 mb-0 py-2 alert-success" role="alert">
                                <i class="fa fa-check-circle"></i>
                                Invoice successfully fiscalised. Verification code: FH-2025-00142.
                            </div>

                            {{-- QR CODE PANEL --}}
                            <div class="card mt-3 border-success hidden-print">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://fiscalharmony.co.zw/verify/FH-2025-00142"
                                            alt="Fiscal QR Code" width="100" height="100"
                                            style="border:1px solid #ccc; border-radius:4px">
                                        <div style="font-size:13px; line-height:1.8">
                                            <div>
                                                <strong>Verification Code:</strong>
                                                <span class="badge bg-dark text-white px-2 py-1"
                                                    style="font-family:monospace; font-size:14px">
                                                    FH-2025-00142
                                                </span>
                                            </div>
                                            <div><strong>Fiscal Day:</strong> 2025-05-15</div>
                                            <div><strong>RA Invoice No:</strong> RA-INV-00789456</div>
                                            <small class="text-muted">
                                                Scan QR code or enter verification code at the Revenue Authority portal.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="invoice overflow-auto">
                            <div style="min-width: 600px">
                                <header>
                                    <div class="row">
                                        <div class="col">
                                            <a href="javascript:;">
                                                <img src="{{ asset('images/uploads/company-logo.png') }}" width="200" alt="">
                                            </a>
                                        </div>
                                        <div class="col company-details">
                                            <h4 class="name" style="color: #185FA5">
                                                Gonyeti Transport Ltd
                                            </h4>
                                            <div>123 Samora Machel Ave Workington <br>
                                                Harare, Zimbabwe</div>
                                            <div>
                                                +263 77 123 4567
                                                | +263 71 987 6543
                                                | +263 78 111 2222
                                            </div>
                                            <div>info@gonyetitransport.co.zw</div>
                                            <div>accounts@gonyetitransport.co.zw</div>
                                            <div>billing@gonyetitransport.co.zw</div>
                                            <br>
                                            <div>VAT No.: 1234567890</div>
                                            <div>TIN.: 9876543210</div>
                                            <div>Vendor No.: VND-00045</div>
                                        </div>
                                    </div>

                                    <div style="padding-top: 25px; padding-bottom:15px">
                                        <center>
                                            <h2>FISCAL TAX INVOICE</h2>
                                            <p>Thank you for your business with Gonyeti Transport Ltd</p>
                                        </center>
                                    </div>
                                </header>
                                <main>
                                    <div class="row contacts">
                                        <div class="col invoice-to">
                                            <div class="text-gray-light">BILL TO:</div>
                                            <h6 class="to"><strong>Customer Name: </strong>Harare Freight Distributors (Pvt) Ltd</h6>
                                            <div class="address">
                                                <strong>Customer Address: </strong>
                                                45 Coventry Road Workington, <br>
                                                Harare Zimbabwe
                                            </div>
                                            <div class="email">
                                                <strong>Customer Email: </strong> accounts@hfd.co.zw
                                            </div>
                                            <div class="email">
                                                <strong>Customer Phonenumber: </strong>+263 77 456 7890
                                            </div>
                                            <div class="email">
                                                <strong>Customer VAT No:</strong> 2233445566
                                            </div>
                                            <div class="email">
                                                <strong>Customer TIN No:</strong> 7788990011
                                            </div>
                                        </div>
                                        <div class="col invoice-details">
                                            <div class="date" style="padding-bottom: 3px"><strong>Document No.:</strong> INV-2025-00142</div>
                                            <div class="date" style="padding-bottom: 3px"><strong>S.O No.:</strong> SO-2025-0089</div>
                                            <div class="date" style="padding-bottom: 3px"><strong>P.O No.:</strong> PO-2025-0034</div>
                                            <div class="date" style="padding-bottom: 3px"><strong>PAT No.:</strong> PAT-00056</div>
                                            <div class="date" style="padding-bottom: 3px"><strong>Invoice Date:</strong> 2025-05-15</div>
                                            <div class="date" style="padding-bottom: 3px"><strong>Payment Due:</strong> 2025-06-14</div>
                                            <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> USD</div>
                                        </div>
                                    </div>

                                    <table>
                                        <thead>
                                            <tr>
                                                <th class="text-center"><strong>Description</strong></th>
                                                <th class="text-center"><strong>HS Code</strong></th>
                                                <th class="text-right"><strong>Qty</strong></th>
                                                <th class="text-right"><strong>Unit Price</strong></th>
                                                <th class="text-right"><strong>Amount</strong><small>(Excl)</small></th>
                                                <th class="text-right"><strong>VAT Amount</strong></th>
                                                <th class="text-right"><strong>Total</strong><small>(Incl)</small></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">
                                                    Long Haul Freight<br>
                                                    Trip No. TR-20250501 — Harare to Beit Bridge
                                                </td>
                                                <td class="unit text-center">8704.10</td>
                                                <td class="unit text-right">1</td>
                                                <td class="unit text-right">$1,200.00</td>
                                                <td class="unit text-right">$1,200.00</td>
                                                <td class="unit text-right">$180.00</td>
                                                <td class="unit text-right">$1,380.00</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    Fuel Surcharge<br>
                                                    Beit Bridge route
                                                </td>
                                                <td class="unit text-center">2710.19</td>
                                                <td class="unit text-right">1</td>
                                                <td class="unit text-right">$150.00</td>
                                                <td class="unit text-right">$150.00</td>
                                                <td class="unit text-right">$22.50</td>
                                                <td class="unit text-right">$172.50</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    Cross Border Documentation<br>
                                                    Customs processing fee
                                                </td>
                                                <td class="unit text-center">4909.00</td>
                                                <td class="unit text-right">3</td>
                                                <td class="unit text-right">$30.00</td>
                                                <td class="unit text-right">$90.00</td>
                                                <td class="unit text-right">$13.50</td>
                                                <td class="unit text-right">$103.50</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2">SUB-TOTAL USD <small>(Excl)</small></td>
                                                <td>$1,440.00</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2">VAT TOTAL</td>
                                                <td>$216.00</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2">DISCOUNT Early Payment</td>
                                                <td>5.00 %</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2">INVOICE TOTAL USD</td>
                                                <td>$1,584.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <div class="notices">
                                        <div><strong>Notes / Terms & Conditions</strong></div>
                                        <div class="notice">Payment is due within 30 days of invoice date. All rates are in USD. Late payments attract a 2% monthly surcharge. Please reference the invoice number on all payments.</div>
                                    </div>

                                    <br>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="background-color: white;"><strong>Banking Details</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-left" scope="col">
                                                    <strong>Bank: </strong>CBZ Bank Limited<br>
                                                    <strong>Branch: </strong>Jason Moyo Avenue Branch<br>
                                                    <strong>Branch Code: </strong>003<br>
                                                    <strong>Swift Code: </strong>COBZZWHAXXX<br>
                                                    <strong>Account Type: </strong>Current<br>
                                                    <strong>Account Name: </strong>Gonyeti Transport Ltd<br>
                                                    <strong>Account Number: </strong>01220567890001
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </main>

                                <center>
                                    <footer style="text-align: center; bottom: 0px; left: 0px; right: 0px;">
                                        This document was electronically generated. E&amp;OE.
                                        <br>
                                        <strong style="font-size: 18px;">Powered By</strong> <img src="{{ asset('images/basilmark-logo.png') }}" alt="" style="width: 20%; height:20%">
                                    </footer>
                                </center>
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection