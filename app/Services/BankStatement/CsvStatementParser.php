<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;

/**
 * Expects a CSV export with a header row using (case-insensitive, any order)
 * some combination of: Date, Value Date, Description, Reference, Debit,
 * Credit, Amount, Balance. Either separate Debit/Credit columns or a single
 * signed Amount column (+ = money in, - = money out) is accepted.
 */
class CsvStatementParser implements StatementParserInterface
{
    private const ALIASES = [
        'transaction_date' => ['date', 'transaction date', 'posting date', 'posted date'],
        'value_date'       => ['value date', 'valuedate'],
        'description'      => ['description', 'narrative', 'details', 'particulars', 'memo'],
        'reference'        => ['reference', 'ref', 'reference number', 'cheque number'],
        'external_ref'     => ['transaction id', 'transaction ref', 'external ref', 'bank ref'],
        'debit'            => ['debit', 'withdrawal', 'money out', 'debit amount'],
        'credit'           => ['credit', 'deposit', 'money in', 'credit amount'],
        'amount'           => ['amount'],
        'balance'          => ['balance', 'running balance', 'closing balance'],
    ];

    public function parse(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open statement file: {$filePath}");
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException('Statement CSV has no header row.');
        }

        $columns = $this->mapColumns($header);
        if (!isset($columns['transaction_date']) || !isset($columns['description'])) {
            fclose($handle);
            throw new \RuntimeException('Statement CSV must contain at least a Date and Description column.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $get = fn (string $key) => isset($columns[$key]) ? ($row[$columns[$key]] ?? null) : null;

            $debit  = (float) str_replace(',', '', (string) $get('debit'));
            $credit = (float) str_replace(',', '', (string) $get('credit'));

            if ($amount = $get('amount')) {
                $amount = (float) str_replace(',', '', (string) $amount);
                $debit  = $amount < 0 ? abs($amount) : 0.0;
                $credit = $amount > 0 ? $amount : 0.0;
            }

            $rows[] = [
                'transaction_date' => Carbon::parse($get('transaction_date')),
                'value_date'       => $get('value_date') ? Carbon::parse($get('value_date')) : null,
                'description'      => trim((string) $get('description')),
                'reference'        => $get('reference') ?: null,
                'external_ref'     => $get('external_ref') ?: null,
                'debit'            => $debit,
                'credit'           => $credit,
                'balance'          => $get('balance') !== null && $get('balance') !== ''
                    ? (float) str_replace(',', '', (string) $get('balance'))
                    : null,
            ];
        }

        fclose($handle);

        return $rows;
    }

    /** @return array<string, int> normalized field name => column index */
    private function mapColumns(array $header): array
    {
        $normalized = array_map(fn ($h) => strtolower(trim($h)), $header);
        $columns = [];

        foreach (self::ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $index = array_search($alias, $normalized, true);
                if ($index !== false) {
                    $columns[$field] = $index;
                    break;
                }
            }
        }

        return $columns;
    }
}
