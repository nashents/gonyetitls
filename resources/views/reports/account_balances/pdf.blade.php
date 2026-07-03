<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Account Balances</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Account Balances', 'isPdf' => true])

    @include('reports.account_balances.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Account Balances'])

</body>
</html>
