<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Account Transactions</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Account Transactions (General Ledger)'])

    @include('reports.account_transactions.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Account Transactions'])

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
