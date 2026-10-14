<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ZIMRA Sales &amp; Purchases Schedule</title>
    @include('reports.shared._styles')
    @include('reports.zimra_schedule.partials.styles')
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { margin: 20px 25px; }
    </style>
</head>
<body>

    @include('reports.shared._header', ['title' => 'ZIMRA Sales & Purchases Schedule'])

    <br>

    @include('reports.zimra_schedule.partials.schedule', ['links' => false])

    @include('reports.shared._footer', ['reportTitle' => 'ZIMRA Sales & Purchases Schedule'])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
