<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Customs Turnaround Time</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Customs Turnaround Time', 'from' => $from, 'to' => $to, 'isPdf' => true])

    @include('freight.reports.customs_turnaround.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Customs Turnaround Time', 'from' => $from, 'to' => $to])

</body>
</html>
