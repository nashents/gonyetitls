<?php

namespace App\Services\BankStatement;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatementImportService
{
    /**
     * @param string $filePath   Path to the uploaded statement file on disk.
     * @param string $fileName  Original file name (for display/audit only).
     * @param string|null $format One of 'csv'|'ofx'|'mt940'; guessed from the
     *                            extension when omitted.
     */
    public function import(BankAccount $bankAccount, string $filePath, string $fileName, ?string $format = null): BankStatement
    {
        $format = $format ?: $this->guessFormat($fileName);
        $parser = $this->resolveParser($format);

        $statement = new BankStatement();
        $statement->company_id = $bankAccount->company_id;
        $statement->bank_account_id = $bankAccount->id;
        $statement->currency_id = $bankAccount->currency_id;
        $statement->file_name = $fileName;
        $statement->file_format = $format;
        $statement->imported_by_id = Auth::id();
        $statement->status = 'processing';
        $statement->save();

        try {
            $rows = $parser->parse($filePath);
        } catch (\Throwable $e) {
            $statement->status = 'failed';
            $statement->error_message = $e->getMessage();
            $statement->save();

            throw $e;
        }

        DB::transaction(function () use ($statement, $bankAccount, $rows) {
            $imported = 0;
            $duplicates = 0;
            $minDate = null;
            $maxDate = null;
            $lastBalance = null;

            foreach ($rows as $row) {
                $hash = $this->dedupeHash($bankAccount->id, $row);

                $exists = BankStatementLine::where('bank_account_id', $bankAccount->id)
                    ->where('dedupe_hash', $hash)
                    ->exists();

                if ($exists) {
                    $duplicates++;
                    continue;
                }

                BankStatementLine::create([
                    'bank_statement_id' => $statement->id,
                    'bank_account_id'   => $bankAccount->id,
                    'transaction_date'  => $row['transaction_date'],
                    'value_date'        => $row['value_date'],
                    'description'       => $row['description'],
                    'reference'         => $row['reference'],
                    'external_ref'      => $row['external_ref'],
                    'debit'             => $row['debit'],
                    'credit'            => $row['credit'],
                    'balance'           => $row['balance'],
                    'dedupe_hash'       => $hash,
                    'status'            => 'unmatched',
                ]);

                $imported++;
                $minDate = $minDate === null ? $row['transaction_date'] : $minDate->min($row['transaction_date']);
                $maxDate = $maxDate === null ? $row['transaction_date'] : $maxDate->max($row['transaction_date']);
                if ($row['balance'] !== null) {
                    $lastBalance = $row['balance'];
                }
            }

            $statement->imported_count = $imported;
            $statement->duplicate_count = $duplicates;
            $statement->period_start = $minDate;
            $statement->period_end = $maxDate;
            $statement->closing_balance = $lastBalance;
            $statement->status = 'completed';
            $statement->save();
        });

        return $statement->fresh();
    }

    private function dedupeHash(int $bankAccountId, array $row): string
    {
        if ($row['external_ref']) {
            return sha1($bankAccountId . '|' . $row['external_ref']);
        }

        return sha1(implode('|', [
            $bankAccountId,
            $row['transaction_date']->toDateString(),
            $row['debit'],
            $row['credit'],
            trim(strtolower($row['description'])),
        ]));
    }

    private function guessFormat(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($extension) {
            'ofx'          => 'ofx',
            'sta', 'mt940', 'swi' => 'mt940',
            default        => 'csv',
        };
    }

    private function resolveParser(string $format): StatementParserInterface
    {
        return match ($format) {
            'ofx'   => new OfxStatementParser(),
            'mt940' => new Mt940StatementParser(),
            default => new CsvStatementParser(),
        };
    }
}
