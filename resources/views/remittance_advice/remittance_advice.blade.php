<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <title>Remittance Advice</title>


@include('includes.css')

<style>
    /* includes.css forces table th to white-space:nowrap and 15px padding, which
       pushes this 7-column table wider than the printable page; combined with
       .invoice{overflow:hidden} under @media print, the right-most column gets
       clipped instead of wrapping. Override with higher-specificity ID selector. */
    #invoice table th { white-space: normal !important; }
    #invoice table th, #invoice table td { padding: 8px !important; }
</style>

</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice"  style="font-size: 16px">
                <div class="invoice overflow-auto">
                    <div style="margin-left: -30px; margin-right:-30px">
                        @include('remittance_advice._advice', ['forPdf' => true])
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
