<?php

namespace Tests\Feature\Sage;

use App\Integrations\SageIntacct\SageXmlDriver;
use App\Models\CompanyIntegration;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\TripExpense;
use App\Models\Trip;
use App\Models\Product;
use App\Models\Tax;
use App\Services\Sage\Mappers\SageEmployeeMapper;
use App\Services\Sage\Mappers\SageExpenseItemMapper;
use App\Services\Sage\Mappers\SageProductItemMapper;
use App\Services\Sage\Mappers\SageRequisitionMapper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase 3 (Trip expenses → Purchase Requisitions). Sage is mocked via Http::fake;
 * no database is touched (unsaved model instances only).
 */
class SagePurchasingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('sageintacct.project.location_id', 'E100');
        config()->set('sageintacct.project.department_id', 'D2-1');
        config()->set('sageintacct.purchasing.requisition_type', 'Purchase requisition');
        config()->set('sageintacct.purchasing.line_unit', 'Each');
        config()->set('sageintacct.purchasing.entity_id', 'E100');
        config()->set('sageintacct.purchasing.exchange_rate_type', 'Intacct Daily Rate');
    }

    protected function driver(): SageXmlDriver
    {
        return new SageXmlDriver(new CompanyIntegration([
            'config'      => ['driver' => 'xml'],
            'credentials' => ['sender_id' => 'S', 'sender_password' => 'SP', 'user_id' => 'U', 'company_id' => 'C', 'user_password' => 'UP'],
        ]));
    }

    protected function successXml(string $key = 'K'): string
    {
        return '<?xml version="1.0"?><response><control><status>success</status></control><operation>'
            . '<authentication><status>success</status></authentication>'
            . "<result><status>success</status><key>{$key}</key></result></operation></response>";
    }

    // ── Driver ───────────────────────────────────────────────────

    /** @test */
    public function it_builds_a_requisition_in_the_correct_schema_order()
    {
        Http::fake(['*' => Http::response($this->successXml('Purchase requisition-PR1'), 200)]);

        $header = [
            'transactiontype' => 'Purchase requisition', 'datecreated' => '2026-03-01',
            'vendorid' => 'V1', 'referenceno' => 'TRIP-FPT1-V5', 'datedue' => '2026-03-01',
            'contactname' => 'Acme(VV1)', 'currency' => 'USD',
            'exchratetype' => 'Intacct Daily Rate', 'entityid' => 'E100',
        ];
        $lines = [[
            'itemid' => 'EXP-1', 'itemdesc' => 'Fuel', 'quantity' => 1, 'unit' => 'Each',
            'price' => '100.00', 'locationid' => 'E100', 'departmentid' => 'D2-1',
            'projectid' => 'MAN1', 'employeeid' => 'EMP-9', 'classid' => 'FHH00001',
        ]];

        $res = $this->driver()->createRequisition($header, $lines);
        $this->assertTrue($res['success']);
        $this->assertSame('Purchase requisition-PR1', $res['data']['id']);

        Http::assertSent(function ($r) {
            $b = $r->body();
            // header order + required returnto/payto
            $ordered = strpos($b, '<transactiontype>') < strpos($b, '<vendorid>')
                && strpos($b, '<vendorid>') < strpos($b, '<returnto>')
                && strpos($b, '<returnto>') < strpos($b, '<payto>')
                && strpos($b, '<payto>') < strpos($b, '<potransitems>');
            // line: employeeid before classid
            $lineOk = strpos($b, '<employeeid>EMP-9</employeeid>') < strpos($b, '<classid>FHH00001</classid>');
            // requisition is scoped to the operating entity via the login, and a
            // currency-bearing requisition carries an exchange-rate type (after
            // currency, before potransitems) so Sage resolves the rate.
            $entityScoped = str_contains($b, '<locationid>E100</locationid></login>');
            $exchOk = strpos($b, '<currency>USD</currency>') < strpos($b, '<exchratetype>Intacct Daily Rate</exchratetype>')
                && strpos($b, '<exchratetype>Intacct Daily Rate</exchratetype>') < strpos($b, '<potransitems>');
            return $ordered && $lineOk && $entityScoped && $exchOk
                && str_contains($b, '<create_potransaction>')
                && str_contains($b, '<transactiontype>Purchase requisition</transactiontype>')
                && str_contains($b, '<vendorid>V1</vendorid>')
                && str_contains($b, '<returnto><contactname>Acme(VV1)</contactname></returnto>')
                && str_contains($b, '<payto><contactname>Acme(VV1)</contactname></payto>')
                && str_contains($b, '<itemid>EXP-1</itemid>')
                && str_contains($b, '<unit>Each</unit>')
                && str_contains($b, '<price>100.00</price>')
                && str_contains($b, '<projectid>MAN1</projectid>');
        });
    }

    /** @test */
    public function it_emits_header_custom_fields_for_diesel_and_dispatch()
    {
        Http::fake(['*' => Http::response($this->successXml('PR - Diesel-PRD1'), 200)]);

        $header = [
            'transactiontype' => 'PR - Diesel', 'datecreated' => '2026-03-01',
            'vendorid' => 'FSTN-1', 'referenceno' => 'FUEL-1', 'datedue' => '2026-03-01',
            'contactname' => 'Station(VFSTN-1)', 'currency' => 'ZAR',
            'exchratetype' => 'Intacct Daily Rate', 'entityid' => 'E100',
            'customfields' => ['REG' => 'AAZ 0790', 'Driver' => 'John Doe'],
        ];
        $lines = [[
            'itemid' => 'ITM-DIESEL', 'itemdesc' => 'Diesel', 'quantity' => 400, 'unit' => 'Each',
            'price' => '1.50', 'locationid' => 'E100', 'departmentid' => 'D2-1', 'projectid' => 'MAN9',
        ]];

        $res = $this->driver()->createRequisition($header, $lines);
        $this->assertTrue($res['success']);

        Http::assertSent(function ($r) {
            $b = $r->body();
            $cf = '<customfields><customfield><customfieldname>REG</customfieldname><customfieldvalue>AAZ 0790</customfieldvalue></customfield>'
                . '<customfield><customfieldname>Driver</customfieldname><customfieldvalue>John Doe</customfieldvalue></customfield></customfields>';
            return str_contains($b, '<transactiontype>PR - Diesel</transactiontype>')
                && str_contains($b, $cf)
                && strpos($b, '<customfields>') < strpos($b, '<potransitems>')     // custom fields before lines
                && str_contains($b, '<locationid>E100</locationid></login>');       // entity-scoped
        });
    }

    /** @test */
    public function it_builds_a_sales_transaction_job_card()
    {
        Http::fake(['*' => Http::response($this->successXml('Internal Job Card-IJC-1'), 200)]);

        $header = [
            'transactiontype' => 'Internal Job Card', 'datecreated' => '2026-03-01',
            'customerid' => 'Sub-00007', 'referenceno' => 'JC-FHT001', 'datedue' => '2026-03-01',
            'currency' => 'ZAR', 'exchratetype' => 'Intacct Daily Rate', 'entityid' => 'E100',
        ];
        $lines = [[
            'itemid' => 'PRD-9', 'quantity' => 2, 'unit' => 'Each', 'price' => '50.00',
            'locationid' => 'E100', 'departmentid' => 'D2-1',
            'projectid' => 'FHH00002', 'classid' => 'FHH00002', 'memo' => 'Brake pads',
        ]];

        $res = $this->driver()->createSalesTransaction($header, $lines);
        $this->assertTrue($res['success']);
        $this->assertSame('Internal Job Card-IJC-1', $res['data']['id']);

        Http::assertSent(function ($r) {
            $b = $r->body();
            $ordered = strpos($b, '<transactiontype>') < strpos($b, '<customerid>')
                && strpos($b, '<customerid>') < strpos($b, '<sotransitems>');
            // Line schema order: departmentid, memo, projectid, classid (memo must
            // precede projectid/classid — Sage rejects memo after classid).
            $lineOrder = strpos($b, '<departmentid>D2-1</departmentid>') < strpos($b, '<memo>Brake pads</memo>')
                && strpos($b, '<memo>Brake pads</memo>') < strpos($b, '<projectid>FHH00002</projectid>')
                && strpos($b, '<projectid>FHH00002</projectid>') < strpos($b, '<classid>FHH00002</classid>');
            return $ordered && $lineOrder
                && str_contains($b, '<create_sotransaction>')
                && str_contains($b, '<transactiontype>Internal Job Card</transactiontype>')
                && str_contains($b, '<customerid>Sub-00007</customerid>')
                && str_contains($b, '<sotransitem><itemid>PRD-9</itemid><quantity>2</quantity><unit>Each</unit><price>50.00</price>')
                && str_contains($b, '<projectid>FHH00002</projectid>')     // horse project on the line
                && str_contains($b, '<classid>FHH00002</classid>')          // horse class on the line
                && str_contains($b, '<locationid>E100</locationid></login>');   // entity-scoped
        });
    }

    /** @test */
    public function it_appends_lines_to_an_existing_sales_transaction()
    {
        Http::fake(['*' => Http::response($this->successXml('Internal Job Card-IJC-1'), 200)]);

        $lines = [[
            'itemid' => 'PRD-9', 'quantity' => 1, 'unit' => 'Each', 'price' => '160.00',
            'locationid' => 'E100', 'departmentid' => 'D2-1', 'memo' => 'AIR BAG BPW',
        ]];

        $res = $this->driver()->appendSalesTransactionLines('276223', $lines, 'E100');
        $this->assertTrue($res['success']);

        Http::assertSent(function ($r) {
            $b = $r->body();
            return str_contains($b, '<update_sotransaction key="276223">')
                && str_contains($b, '<updatesotransitems><sotransitem><itemid>PRD-9</itemid>')
                && str_contains($b, '</sotransitem></updatesotransitems></update_sotransaction>')
                && str_contains($b, '<locationid>E100</locationid></login>');   // entity-scoped
        });
    }

    /** @test */
    public function it_creates_an_item_and_an_employee()
    {
        Http::fake(['*' => Http::response($this->successXml('X'), 200)]);

        $this->driver()->createItem(['id' => 'EXP-1', 'name' => 'Fuel', 'type' => 'Non-Inventory', 'taxable' => 'true']);
        Http::assertSent(fn ($r) => str_contains($r->body(), '<create><ITEM>')
            && str_contains($r->body(), '<ITEMID>EXP-1</ITEMID>')
            && str_contains($r->body(), '<ITEMTYPE>Non-Inventory</ITEMTYPE>'));

        $this->driver()->createEmployee(['id' => 'EMP-9', 'contactname' => 'Joe (EMP-9)', 'departmentid' => 'D2-1', 'locationid' => 'E100']);
        Http::assertSent(fn ($r) => str_contains($r->body(), '<create><EMPLOYEE>')
            && str_contains($r->body(), '<EMPLOYEEID>EMP-9</EMPLOYEEID>')
            && str_contains($r->body(), '<PERSONALINFO><CONTACTNAME>Joe (EMP-9)</CONTACTNAME></PERSONALINFO>')
            && str_contains($r->body(), '<LOCATIONID>E100</LOCATIONID>'));
    }

    // ── Mappers ──────────────────────────────────────────────────

    /** @test */
    public function expense_maps_to_item()
    {
        $e = new Expense(['name' => 'Fuel', 'type' => 'variable']);
        $e->id = 7;

        $this->assertSame('EXP-7', SageExpenseItemMapper::itemId($e));
        $this->assertSame('Fuel', SageExpenseItemMapper::name($e));

        $payload = SageExpenseItemMapper::map($e);
        $this->assertSame('EXP-7', $payload['id']);
        $this->assertSame('Fuel', $payload['name']);
        $this->assertSame('Non-Inventory', $payload['type']);
    }

    /** @test */
    public function employee_maps_with_unique_contact_name()
    {
        $emp = new Employee(['employee_number' => 'FHE001', 'name' => 'Peter', 'surname' => 'Ngwarai']);
        $emp->id = 3;
        $emp->status = 1;

        $this->assertSame('FHE001', SageEmployeeMapper::employeeId($emp));
        $this->assertSame('Peter Ngwarai', SageEmployeeMapper::fullName($emp));
        $this->assertSame('Peter Ngwarai (FHE001)', SageEmployeeMapper::contactName($emp));

        $payload = SageEmployeeMapper::map($emp);
        $this->assertSame('FHE001', $payload['id']);
        $this->assertSame('Peter Ngwarai (FHE001)', $payload['contactname']);
        $this->assertSame('D2-1', $payload['departmentid']);
        $this->assertSame('E100', $payload['locationid']);
    }

    /** @test */
    public function product_maps_to_item_with_type_and_tax()
    {
        // Type mapping both directions.
        $this->assertSame('Inventory', SageProductItemMapper::sageType('Inventory'));
        $this->assertSame('Non-Inventory', SageProductItemMapper::sageType('Non Inventory'));
        $this->assertSame('Non Inventory', SageProductItemMapper::gonyetiType('Non-Inventory (Sales only)'));
        $this->assertSame('Inventory', SageProductItemMapper::gonyetiType('Inventory'));

        $product = new Product(['name' => 'Brake Pads', 'type' => 'Non Inventory']);
        $product->id = 42;
        $product->product_number = 'GYP00042';
        $product->gl_group = 'INSURANCE';                       // round-tripped from Sage
        $product->setRelation('tax', new Tax(['name' => 'Standard Rate']));

        $payload = SageProductItemMapper::map($product);
        $this->assertSame('GYP00042', $payload['id']);        // uses product_number
        $this->assertSame('Brake Pads', $payload['name']);
        $this->assertSame('Non-Inventory', $payload['type']);
        $this->assertSame('Standard Rate', $payload['tax_group']); // linked Tax name
        $this->assertSame('true', $payload['taxable']);
        $this->assertSame('INSURANCE', $payload['gl_group']);  // the product's own GL group
    }

    /** @test */
    public function product_without_tax_or_number_falls_back()
    {
        $product = new Product(['name' => 'Ad-hoc Service', 'type' => 'Inventory']);
        $product->id = 7;

        $payload = SageProductItemMapper::map($product);
        $this->assertSame('PRD-7', $payload['id']);       // prefix + id fallback
        $this->assertSame('Inventory', $payload['type']);
        // No product tax → falls back to the item default tax group (so it still
        // resolves a tax schedule on PO / receipt / requisition lines).
        $this->assertSame(config('sageintacct.item.tax_group'), $payload['tax_group']);
        // No product GL group → inventory items fall back to the configured default.
        $this->assertSame('Inventory', $payload['gl_group']);
    }

    /** @test */
    public function requisition_header_and_line_are_mapped()
    {
        $trip = new Trip();
        $trip->id = 627;
        $trip->trip_number = 'FPT00627';
        $trip->start_date = '2026-03-01';

        $this->assertSame('TRIP-FPT00627-V5', SageRequisitionMapper::referenceNo($trip, 5));

        $header = SageRequisitionMapper::header($trip, 5, 'FPV00081', 'Juvenille(VFPV00081)', 'USD');
        $this->assertSame('Purchase requisition', $header['transactiontype']);
        $this->assertSame('FPV00081', $header['vendorid']);
        $this->assertSame('Juvenille(VFPV00081)', $header['contactname']);
        $this->assertSame('USD', $header['currency']);
        $this->assertSame('2026-03-01', $header['datedue']);
        // Entity scope + exchange-rate type come from config.
        $this->assertSame('E100', $header['entityid']);
        $this->assertSame('Intacct Daily Rate', $header['exchratetype']);

        $expense = new TripExpense(['amount' => 250]);
        $expense->setRelation('expense', new Expense(['name' => 'Toll']));
        $line = SageRequisitionMapper::line($expense, 'EXP-7', 'MAN00627', 'FHH00001', 'EMP-3');
        $this->assertSame('EXP-7', $line['itemid']);
        $this->assertSame('Toll', $line['itemdesc']);
        $this->assertSame(1, $line['quantity']);
        $this->assertSame('Each', $line['unit']);
        $this->assertSame('250.00', $line['price']);
        $this->assertSame('MAN00627', $line['projectid']);
        $this->assertSame('FHH00001', $line['classid']);
        $this->assertSame('EMP-3', $line['employeeid']);
        $this->assertSame('D2-1', $line['departmentid']);
    }
}
