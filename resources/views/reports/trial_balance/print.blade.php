<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Trial Balance</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Trial Balance', 'from' => $date_from, 'to' => $date_to])

    @include('reports.trial_balance.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Trial Balance', 'from' => $date_from, 'to' => $date_to])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
