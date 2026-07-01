<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cash Flow Statement</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Cash Flow Statement'])

    @include('reports.cashflows.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Cash Flow Statement'])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
