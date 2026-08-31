<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Unbilled Freight Costs Aging</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Unbilled Freight Costs Aging', 'asOfDate' => $as_of_date, 'isPdf' => true])

    @include('freight.reports.unbilled_costs.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Unbilled Freight Costs Aging', 'asOfDate' => $as_of_date])

</body>
</html>
