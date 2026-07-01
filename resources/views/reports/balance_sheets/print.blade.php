<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Balance Sheet</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Balance Sheet', 'asOfDate' => $as_of_date])

    @include('reports.balance_sheets.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Balance Sheet', 'asOfDate' => $as_of_date])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
