<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Income by Customer</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Income by Customer', 'selectedType' => $selectedType ?? null, 'isPdf' => true])

    @include('reports.income_by_customers.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Income by Customer'])

</body>
</html>
