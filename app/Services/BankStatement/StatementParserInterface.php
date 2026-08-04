<?php

namespace App\Services\BankStatement;

interface StatementParserInterface
{
    /**
     * Parse a statement file into a flat list of normalized transaction rows:
     * ['transaction_date' => Carbon, 'value_date' => ?Carbon, 'description' => string,
     *  'reference' => ?string, 'external_ref' => ?string, 'debit' => float, 'credit' => float,
     *  'balance' => ?float]
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $filePath): array;
}
