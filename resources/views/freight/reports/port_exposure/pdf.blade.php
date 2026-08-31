<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Port & Demurrage Exposure</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Port & Demurrage/Detention Exposure', 'isPdf' => true, 'asOfDate' => $as_of_date])

    @include('freight.reports.port_exposure.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Port & Demurrage Exposure', 'asOfDate' => $as_of_date])

</body>
</html>
