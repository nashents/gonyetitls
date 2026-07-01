<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profit and Loss</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Profit and Loss'])

    @include('reports.income_statements.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Profit and Loss'])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
