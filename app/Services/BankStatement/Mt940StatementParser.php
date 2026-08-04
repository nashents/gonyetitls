<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;

/**
 * Minimal SWIFT MT940 parser. Reconstructs :86: narrative continuation lines
 * onto their owning tag, then reads :61: (statement line) / :86: (info to
 * account owner) pairs into normalized rows. Balance tags (:60F:/:62F:) are
 * intentionally not parsed - opening/closing balance is entered by the user
 * when starting the reconciliation.
 */
class Mt940StatementParser implements StatementParserInterface
{
    public function parse(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Unable to open statement file: {$filePath}");
        }

        $tagLines = $this->reconstructTagLines($content);

        $rows = [];
        $pending = null;

        foreach ($tagLines as $line) {
            if (preg_match('/^:61:(.*)$/s', $line, $m)) {
                if ($pending) {
                    $rows[] = $pending;
                }
                $pending = $this->parseStatementLine($m[1]);
                continue;
            }

            if (preg_match('/^:86:(.*)$/s', $line, $m) && $pending) {
                $pending['description'] = trim(preg_replace('/\s+/', ' ', $m[1]));
                continue;
            }
        }

        if ($pending) {
            $rows[] = $pending;
        }

        return array_values(array_filter($rows, fn ($r) => $r['transaction_date'] !== null));
    }

    /** Joins non-tag continuation lines onto the preceding :XX: tag line. */
    private function reconstructTagLines(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $tagLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^:\w+:/', $line)) {
                $tagLines[] = $line;
            } elseif (!empty($tagLines) && trim($line) !== '') {
                $tagLines[count($tagLines) - 1] .= ' ' . $line;
            }
        }

        return $tagLines;
    }

    private function parseStatementLine(string $body): array
    {
        // YYMMDD [MMDD] (C|D|RC|RD) amount(comma-decimal) [type code] [ref][//bank ref]
        if (!preg_match('/^(\d{6})(\d{4})?(RC|RD|C|D)(\d+,\d{0,2})([A-Z0-9]{0,4})?(.*)$/s', trim($body), $m)) {
            return [
                'transaction_date' => null,
                'value_date'       => null,
                'description'      => '',
                'reference'        => null,
                'external_ref'     => null,
                'debit'            => 0.0,
                'credit'           => 0.0,
                'balance'          => null,
            ];
        }

        [, $valueDate, , $mark, $amountStr, , $remainder] = $m;

        $amount = (float) str_replace(',', '.', rtrim($amountStr, ','));
        $isDebit = in_array($mark, ['D', 'RD'], true);

        $remainder = trim($remainder);
        [$customerRef, $bankRef] = array_pad(explode('//', $remainder, 2), 2, null);

        return [
            'transaction_date' => Carbon::createFromFormat('ymd', $valueDate),
            'value_date'       => null,
            'description'      => '',
            'reference'        => $customerRef ?: null,
            'external_ref'     => $bankRef ?: null,
            'debit'            => $isDebit ? $amount : 0.0,
            'credit'           => $isDebit ? 0.0 : $amount,
            'balance'          => null,
        ];
    }
}
