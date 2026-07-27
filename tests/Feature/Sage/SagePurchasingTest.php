<?php

namespace Tests\Feature\Sage;

use App\Integrations\SageIntacct\SageXmlDriver;
use App\Models\CompanyIntegration;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\TripExpense;
use App\Models\Trip;
use App\Services\Sage\Mappers\SageEmployeeMapper;
use App\Services\Sage\Mappers\SageExpenseItemMapper;
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
            return $ordered && $lineOk
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
