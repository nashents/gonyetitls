<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title')</title>
  {{-- <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}" media="screen" >
  <link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}" media="screen" > --}}
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <script src="https://kit.fontawesome.com/0154e08647.js" crossorigin="anonymous"></script>
    @if (Auth::user()->employee->company)
        <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
        <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
    {{-- <link rel="stylesheet" href="{{asset('css/layout.css')}}"> --}}
    <style>
        .table-bordered {
        border: 1px solid #000;   /* border around the table itself */
         border-collapse: collapse;    
        }

        .table-bordered th,
        .table-bordered td {
        border: 1px solid #000;   /* borders for each cell */
        }
    </style>
    <style>
            /* General table styling overrides for this print page */
            .invoice-table {
                width: 100%;
                border-collapse: collapse;
                table-layout: auto; /* Let browser size columns naturally */
            }

            .invoice-table th,
            .invoice-table td {
                padding: 4px 6px;
                vertical-align: top;
            }

            /* Make description wrap, but keep numeric columns compact */
            .invoice-table .col-description {
                white-space: normal;
                word-wrap: break-word;
                word-break: break-word;
            }

            .invoice-table .col-hs,
            .invoice-table .col-qty,
            .invoice-table .col-unit,
            .invoice-table .col-total-excl,
            .invoice-table .col-vat,
            .invoice-table .col-total-incl {
                white-space: nowrap;
            }

        /* Remove borders everywhere in the table */
            .invoice-table,
            .invoice-table td,
            .invoice-table th {
                border: none !important;
            }

            /* Footer rows ONLY: add borders back */
            .invoice-table tfoot td,
            .invoice-table tfoot th {
                border-top: 0.5px  #000 !important;
                border-bottom: 0.5px solid #000 !important;
                font-weight: bold;
                padding-top: 6px;
                padding-bottom: 6px;
            }

            /* Make footer totals stand out slightly more */
            .invoice-table tfoot tr:not(:last-child) td {
                border-bottom: 1px solid #000 !important;
            }

            /* Description wrapping stays clean */
            .invoice-table .col-description {
                white-space: normal;
                word-break: break-word;
            }

            /* Try to keep rows together on one page when printing */
            @media print {
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .invoice.overflow-auto {
                    overflow: visible !important; /* Stop clipping content on print */
                }

                .invoice-table tr,
                .invoice-table td,
                .invoice-table th {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                thead { 
                    display: table-header-group; 
                }

                tfoot { 
                    display: table-footer-group; 
                }

                /* Let the page use space properly */
                @page {
                    margin: 15mm;
                }

                /* Footer: let it flow naturally instead of fixed to avoid overlap */
                .print-footer {
                    position: static !important;
                }
            }

            @media print {
            body * {
                visibility: hidden;
            }
            #print-area, #print-area * {
                visibility: visible;
            }
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>

    <style>
        /* Hide everything except #print-area when printing */
        @media print {
            body * {
                visibility: hidden;
            }

            #print-area, 
            #print-area * {
                visibility: visible;
            }

            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Hide toolbar/buttons on print */
            .hidden-print {
                display: none !important;
            }
        }
    </style>

    <style>
@media print {

    /* Keep original background colors when printing */
    html, body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Force table header and shaded rows to keep colors */
    .invoice-table thead th,
    .invoice-table tfoot td,
    .invoice-table tfoot th,
    .invoice-table tbody tr.shaded-row {
        background-color: inherit !important;
    }

    /* Keep your borderless / soft border design */
    .invoice-table,
    .invoice-table th,
    .invoice-table td {
        border: none !important;       /* Prevent Chrome from adding black borders */
    }

    /* Remove toolbar when printing */
    .hidden-print {
        display: none !important;
    }

    /* Footer must stay borderless */
    footer, footer * {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
}
</style>

    @yield('extra-css')
    @include('includes.css')
    @livewireStyles
    @stack('styles')
</head>
<body>

    @yield('content')
    <script>
        function goBack() {
        window.history.back();
        }
    </script>
    @yield('extra-js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 10000,
            timerProgressBar:true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        window.addEventListener('alert',({detail:{type,message}})=>{
            Toast.fire({
                icon:type,
                title:message
            })
        })
    </script>
    {{-- <script>
        function printSection(sectionId) {
            let printContent = document.getElementById(sectionId).innerHTML;
            let originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;

            // Reload scripts for safety
            window.location.reload();
        }
    </script> --}}
    <script>
        function printSection() {
            window.print();
        }
    </script>
    @yield('timeout-js')
    <script src="{{asset('js/jquery/jquery-2.2.4.min.js')}}"></script>
    <script src="{{asset('js/jquery-ui/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/bootstrap/bootstrap.min.js')}}"></script>
    @livewireScripts
</body>
</html>
