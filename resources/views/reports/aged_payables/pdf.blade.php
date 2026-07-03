<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Aged Payables</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Aged Payables', 'asOfDate' => $as_of_date, 'isPdf' => true])

    @include('reports.aged_payables.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Aged Payables', 'asOfDate' => $as_of_date])

</body>
</html>
