<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bank Reconciliation</title>
    @include('reports.shared._styles')
</head>
<body>

    @include('reports.shared._header', ['title' => 'Bank Reconciliation Statement', 'company' => $company, 'from' => $from, 'to' => $to, 'isPdf' => true])

    @include('reports.bank_reconciliations.partials.statement')

    @include('reports.shared._footer', ['reportTitle' => 'Bank Reconciliation', 'company' => $company, 'from' => $from, 'to' => $to])

</body>
</html>
