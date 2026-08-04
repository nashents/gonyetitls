<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;

/**
 * Minimal OFX parser covering both OFX v1 (SGML - aggregate tags like
 * <STMTTRN> are closed, leaf tags like <TRNAMT> are not) and OFX v2 (well
 * formed XML) for the fields a bank reconciliation needs: date, amount,
 * description and the bank's own transaction id (FITID, used for dedupe).
 */
class OfxStatementParser implements StatementParserInterface
{
    public function parse(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Unable to open statement file: {$filePath}");
        }

        if (!preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/is', $content, $matches)) {
            throw new \RuntimeException('No <STMTTRN> transactions found in OFX file.');
        }

        $rows = [];
        foreach ($matches[1] as $block) {
            $amount = (float) $this->extract($block, 'TRNAMT');
            $date   = $this->extract($block, 'DTPOSTED');

            $rows[] = [
                'transaction_date' => $date ? Carbon::createFromFormat('Ymd', substr($date, 0, 8)) : null,
                'value_date'       => null,
                'description'      => $this->extract($block, 'NAME') ?? $this->extract($block, 'MEMO') ?? '',
                'reference'        => $this->extract($block, 'CHECKNUM'),
                'external_ref'     => $this->extract($block, 'FITID'),
                'debit'            => $amount < 0 ? abs($amount) : 0.0,
                'credit'           => $amount > 0 ? $amount : 0.0,
                'balance'          => null,
            ];
        }

        return array_values(array_filter($rows, fn ($r) => $r['transaction_date'] !== null));
    }

    private function extract(string $block, string $tag): ?string
    {
        if (preg_match('/<' . $tag . '>([^<\r\n]+)/i', $block, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
