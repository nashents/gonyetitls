<?php

namespace Tests\Feature\BankStatement;

use App\Services\BankStatement\CsvStatementParser;
use App\Services\BankStatement\Mt940StatementParser;
use App\Services\BankStatement\OfxStatementParser;
use Tests\TestCase;

/**
 * Pure parsing tests - no database - for the three statement formats bank
 * reconciliation can import. Mirrors the "pure mapping tests" style used for
 * the Sage mappers.
 */
class StatementParserTest extends TestCase
{
    private function fixture(string $contents, string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'stmt') . '.' . $extension;
        file_put_contents($path, $contents);

        return $path;
    }

    /** @test */
    public function csv_parser_reads_debit_credit_columns()
    {
        $file = $this->fixture(
            "Date,Description,Reference,Debit,Credit,Balance\n" .
            "2026-07-05,Opening deposit,DEP001,,1000.00,1000.00\n" .
            "2026-07-10,Office supplies,CHQ100,150.00,,850.00\n",
            'csv'
        );

        $rows = (new CsvStatementParser())->parse($file);

        $this->assertCount(2, $rows);
        $this->assertSame('2026-07-05', $rows[0]['transaction_date']->toDateString());
        $this->assertSame('Opening deposit', $rows[0]['description']);
        $this->assertEquals(1000.00, $rows[0]['credit']);
        $this->assertEquals(0.0, $rows[0]['debit']);
        $this->assertEquals(150.00, $rows[1]['debit']);
        $this->assertEquals(850.00, $rows[1]['balance']);
    }

    /** @test */
    public function csv_parser_splits_a_signed_amount_column()
    {
        $file = $this->fixture(
            "Date,Description,Amount\n" .
            "2026-07-05,Deposit,1000\n" .
            "2026-07-10,Withdrawal,-150.50\n",
            'csv'
        );

        $rows = (new CsvStatementParser())->parse($file);

        $this->assertEquals(1000.0, $rows[0]['credit']);
        $this->assertEquals(0.0, $rows[0]['debit']);
        $this->assertEquals(150.50, $rows[1]['debit']);
        $this->assertEquals(0.0, $rows[1]['credit']);
    }

    /** @test */
    public function csv_parser_skips_blank_rows_and_requires_date_and_description()
    {
        $file = $this->fixture("Amount,Balance\n100,200\n", 'csv');

        $this->expectException(\RuntimeException::class);
        (new CsvStatementParser())->parse($file);
    }

    /** @test */
    public function ofx_parser_reads_stmttrn_blocks()
    {
        $file = $this->fixture(<<<OFX
OFXHEADER:100
DATA:OFXSGML
VERSION:102

<OFX>
<BANKMSGSRSV1>
<STMTTRNRS>
<STMTRS>
<BANKTRANLIST>
<STMTTRN>
<TRNTYPE>CREDIT
<DTPOSTED>20260705120000
<TRNAMT>1000.00
<FITID>FIT001
<NAME>Opening deposit
</STMTTRN>
<STMTTRN>
<TRNTYPE>DEBIT
<DTPOSTED>20260710120000
<TRNAMT>-150.00
<FITID>FIT002
<NAME>Office supplies
</STMTTRN>
</BANKTRANLIST>
</STMTRS>
</STMTTRNRS>
</BANKMSGSRSV1>
</OFX>
OFX,
            'ofx'
        );

        $rows = (new OfxStatementParser())->parse($file);

        $this->assertCount(2, $rows);
        $this->assertSame('2026-07-05', $rows[0]['transaction_date']->toDateString());
        $this->assertEquals(1000.00, $rows[0]['credit']);
        $this->assertSame('FIT001', $rows[0]['external_ref']);
        $this->assertEquals(150.00, $rows[1]['debit']);
        $this->assertSame('Office supplies', $rows[1]['description']);
    }

    /** @test */
    public function mt940_parser_reads_statement_lines_and_narrative()
    {
        $file = $this->fixture(
            ":20:REF12345\r\n" .
            ":25:1234567890\r\n" .
            ":28C:1/1\r\n" .
            ":60F:C260701USD1000,00\r\n" .
            ":61:260705C1000,00NMSCNONREF//BANKREF1\r\n" .
            ":86:Opening deposit\r\n" .
            ":61:260710D150,00NTRFNONREF//BANKREF2\r\n" .
            ":86:Office supplies\r\n" .
            ":62F:C260710USD850,00\r\n",
            'sta'
        );

        $rows = (new Mt940StatementParser())->parse($file);

        $this->assertCount(2, $rows);
        $this->assertSame('2026-07-05', $rows[0]['transaction_date']->toDateString());
        $this->assertEquals(1000.00, $rows[0]['credit']);
        $this->assertSame('Opening deposit', $rows[0]['description']);
        $this->assertSame('BANKREF1', $rows[0]['external_ref']);
        $this->assertEquals(150.00, $rows[1]['debit']);
        $this->assertSame('Office supplies', $rows[1]['description']);
    }
}
