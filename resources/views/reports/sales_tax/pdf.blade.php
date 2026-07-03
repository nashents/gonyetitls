<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Tax Report</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Sales Tax Report', 'selectedType' => $selectedType ?? null, 'isPdf' => true])

    @include('reports.sales_tax.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Sales Tax Report'])

</body>
</html>
