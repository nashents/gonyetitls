<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Freight Job Profitability</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Freight Job Profitability', 'from' => $from, 'to' => $to, 'isPdf' => true])

    @include('freight.reports.job_profitability.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Freight Job Profitability', 'from' => $from, 'to' => $to])

</body>
</html>
