<?php

namespace Tests\Feature\Sage;

use App\Integrations\SageIntacct\SageXmlDriver;
use App\Models\CompanyIntegration;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Driver-level tests. Sage is fully mocked via Http::fake — the live API is
 * never called. No database is touched (the CompanyIntegration is unsaved).
 */
class SageXmlDriverTest extends TestCase
{
    protected function driver(): SageXmlDriver
    {
        $integration = new CompanyIntegration([
            'config'      => ['driver' => 'xml'],
            'credentials' => [
                'sender_id' => 'S', 'sender_password' => 'SP',
                'user_id' => 'U', 'company_id' => 'C', 'user_password' => 'UP',
            ],
        ]);

        return new SageXmlDriver($integration);
    }

    protected function successXml(string $key = 'KEY123'): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><response><control><status>success</status></control>'
            . '<operation><authentication><status>success</status></authentication>'
            . "<result><status>success</status><key>{$key}</key></result></operation></response>";
    }

    protected function failureXml(string $message): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><response><control><status>success</status></control>'
            . '<operation><authentication><status>success</status></authentication>'
            . "<result><status>failure</status><errormessage><error><description2>{$message}</description2></error></errormessage></result>"
            . '</operation></response>';
    }

    /** @test */
    public function it_creates_a_class_with_parent_and_never_leaks_credentials()
    {
        Http::fake(['*' => Http::response($this->successXml('R1'), 200)]);

        $res = $this->driver()->createClass([
            'id' => 'FHH00001', 'name' => 'ABJ 1034', 'parentid' => 'TRANS-2',
            'description' => 'Volvo FH', 'status' => 'active',
        ]);

        $this->assertTrue($res['success']);
        $this->assertSame('R1', $res['data']['id']);

        Http::assertSent(function ($request) {
            $body = $request->body();
            return str_contains($body, '<CLASSID>FHH00001</CLASSID>')
                && str_contains($body, '<NAME>ABJ 1034</NAME>')
                && str_contains($body, '<PARENTID>TRANS-2</PARENTID>')
                && str_contains($body, '<create><CLASS>');
        });

        // The returned request payload (used for audit) must be credential-free.
        $this->assertStringNotContainsString('password', strtolower($res['request']));
        $this->assertStringNotContainsString('<SENDERID>', strtoupper($res['request']));
    }

    /** @test */
    public function it_updates_a_class_by_id()
    {
        Http::fake(['*' => Http::response($this->successXml(), 200)]);

        $res = $this->driver()->updateClass('FHH00001', ['name' => 'ABJ 1034']);

        $this->assertTrue($res['success']);
        Http::assertSent(fn ($r) => str_contains($r->body(), '<update><CLASS>')
            && str_contains($r->body(), '<CLASSID>FHH00001</CLASSID>'));
    }

    /** @test */
    public function it_creates_a_warehouse_with_nested_location()
    {
        Http::fake(['*' => Http::response($this->successXml('WH-2'), 200)]);

        $res = $this->driver()->createWarehouse([
            'id' => 'WH-2', 'name' => 'Trinitas Stores Room',
            'status' => 'active', 'locationid' => 'E100',
        ]);

        $this->assertTrue($res['success']);
        Http::assertSent(function ($r) {
            $b = $r->body();
            // The multi-entity location link is the NESTED relationship <LOC>, not a
            // flat <LOCATIONID> (which is the warehouse's own id).
            return str_contains($b, '<create><WAREHOUSE>')
                && str_contains($b, '<WAREHOUSEID>WH-2</WAREHOUSEID>')
                && str_contains($b, '<NAME>Trinitas Stores Room</NAME>')
                && str_contains($b, '<LOC><LOCATIONID>E100</LOCATIONID></LOC>')
                && str_contains($b, '<STATUS>active</STATUS>');
        });
    }

    /** @test */
    public function it_creates_a_project_with_category_customer_and_class()
    {
        Http::fake(['*' => Http::response($this->successXml('P1'), 200)]);

        $res = $this->driver()->createProject([
            'id' => 'MAN001', 'name' => 'HARARE TO BEIRA', 'category' => 'Contract',
            'projecttype' => 'TRIPS', 'parentid' => 'FHH00001', 'classid' => 'FHH00001',
            'customerid' => 'FHC00022', 'locationid' => 'E100', 'departmentid' => 'D2-1',
            'status' => 'active',
        ]);

        $this->assertTrue($res['success']);
        Http::assertSent(function ($r) {
            $b = $r->body();
            return str_contains($b, '<create><PROJECT>')
                && str_contains($b, '<PROJECTID>MAN001</PROJECTID>')
                && str_contains($b, '<PROJECTCATEGORY>Contract</PROJECTCATEGORY>')
                && str_contains($b, '<PROJECTTYPE>TRIPS</PROJECTTYPE>')
                && str_contains($b, '<PARENTID>FHH00001</PARENTID>')
                && str_contains($b, '<CUSTOMERID>FHC00022</CUSTOMERID>')
                && str_contains($b, '<CLASSID>FHH00001</CLASSID>')
                && str_contains($b, '<LOCATIONID>E100</LOCATIONID>')
                && str_contains($b, '<DEPARTMENTID>D2-1</DEPARTMENTID>');
        });
    }

    /** @test */
    public function it_returns_a_readable_error_on_sage_failure()
    {
        Http::fake(['*' => Http::response($this->failureXml('Another record with the value already exists'), 200)]);

        $res = $this->driver()->createClass(['id' => 'X', 'name' => 'Y']);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString('already exists', $res['error']);
    }

    /** @test */
    public function it_parses_read_by_query_into_rows()
    {
        $xml = '<?xml version="1.0"?><response><control><status>success</status></control><operation>'
            . '<authentication><status>success</status></authentication><result><status>success</status>'
            . '<data listtype="class" count="1"><class><CLASSID>H0001</CLASSID><NAME>DFL385L</NAME></class></data>'
            . '</result></operation></response>';
        Http::fake(['*' => Http::response($xml, 200)]);

        $res = $this->driver()->readByQuery('CLASS', ['CLASSID', 'NAME'], "NAME = 'DFL385L'");

        $this->assertTrue($res['success']);
        $this->assertCount(1, $res['data']);
        $this->assertSame('H0001', $res['data'][0]['CLASSID']);
        $this->assertSame('DFL385L', $res['data'][0]['NAME']);
    }

    /** @test */
    public function read_by_query_returns_result_id_and_remaining_for_paging()
    {
        $xml = '<?xml version="1.0"?><response><control><status>success</status></control><operation>'
            . '<authentication><status>success</status></authentication><result><status>success</status>'
            . '<data listtype="class" count="2" numremaining="3" resultId="RID99">'
            . '<class><CLASSID>H0001</CLASSID><NAME>ABJ 1034</NAME></class>'
            . '<class><CLASSID>H0002</CLASSID><NAME>ABJ 1110</NAME></class>'
            . '</data></result></operation></response>';
        Http::fake(['*' => Http::response($xml, 200)]);

        $res = $this->driver()->readByQuery('CLASS', ['CLASSID', 'NAME'], "CLASSID LIKE 'H%'");

        $this->assertTrue($res['success']);
        $this->assertCount(2, $res['data']);
        $this->assertSame('RID99', $res['resultId']);
        $this->assertSame(3, $res['remaining']);
    }

    /** @test */
    public function read_more_pages_using_the_result_id()
    {
        $xml = '<?xml version="1.0"?><response><control><status>success</status></control><operation>'
            . '<authentication><status>success</status></authentication><result><status>success</status>'
            . '<data listtype="class" count="1" numremaining="0" resultId="RID99">'
            . '<class><CLASSID>H0003</CLASSID><NAME>AEZ 3124</NAME></class>'
            . '</data></result></operation></response>';
        Http::fake(['*' => Http::response($xml, 200)]);

        $res = $this->driver()->readMore('RID99');

        $this->assertTrue($res['success']);
        $this->assertSame('H0003', $res['data'][0]['CLASSID']);
        $this->assertSame(0, $res['remaining']);
        Http::assertSent(fn ($r) => str_contains($r->body(), '<readMore><resultId>RID99</resultId></readMore>'));
    }

    /** @test */
    public function it_handles_a_transport_failure_gracefully()
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Timed out');
        });

        $res = $this->driver()->createProject(['id' => 'P', 'name' => 'N', 'category' => 'Contract']);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString('Could not reach Sage', $res['error']);
    }
}
