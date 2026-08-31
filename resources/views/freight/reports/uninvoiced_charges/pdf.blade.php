<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Uninvoiced Freight Charges Aging</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Uninvoiced Freight Charges Aging', 'asOfDate' => $as_of_date, 'isPdf' => true])

    @include('freight.reports.uninvoiced_charges.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Uninvoiced Freight Charges Aging', 'asOfDate' => $as_of_date])

</body>
</html>
