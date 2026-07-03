<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase by Vendor</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Purchase by Vendor', 'selectedType' => $selectedType ?? null])

    @include('reports.purchase_by_vendors.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Purchase by Vendor'])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
