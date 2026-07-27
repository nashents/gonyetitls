<?php

namespace Tests\Feature\Sage;

use App\Models\Destination;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Trip;
use App\Services\Sage\Mappers\SageHorseMapper;
use App\Services\Sage\Mappers\SageTrailerMapper;
use App\Services\Sage\Mappers\SageTransporterMapper;
use App\Services\Sage\Mappers\SageTripMapper;
use App\Services\Sage\Support\SageFormat;
use Tests\TestCase;

/**
 * Pure mapping tests — model instances only, no database.
 * Reflects the project-centric model: Transporter/Horse/Trip = Projects,
 * Horse also a top-level (orange) Class, Trailer a green Class.
 */
class SageMapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('sageintacct.project.category', 'Contract');
        config()->set('sageintacct.project.location_id', 'E100');
        config()->set('sageintacct.project.department_id', 'D2-1');
        config()->set('sageintacct.project.types', [
            'transporter' => 'SUBCONTRACTOR', 'horse' => 'SUB - TRUCKS', 'trip' => 'TRIPS',
        ]);
    }

    protected function destination(string $name): Destination
    {
        $d = new Destination();
        $d->name = $name;
        return $d;
    }

    /** @test */
    public function sage_format_helpers_behave()
    {
        $this->assertSame('ABJ 1034', SageFormat::id('  abj 1034 '));
        $this->assertSame('ABC1234', SageFormat::id('ABC#1234!'));
        $this->assertSame('001/01032025', SageFormat::id('001/01032025'));
        $this->assertSame('active', SageFormat::boolStatus(1));
        $this->assertSame('inactive', SageFormat::boolStatus(0));
        $this->assertSame('01/15/2020', SageFormat::date('2020-01-15'));
    }

    /** @test */
    public function horse_class_is_top_level_orange_and_project_is_child_of_transporter()
    {
        $horse = new Horse([
            'horse_number' => 'FHH00001', 'registration_number' => 'ABJ 1034',
            'manufacturer' => 'Volvo', 'model' => 'FH', 'year' => '2020',
        ]);
        $horse->status = 1;

        // CLASS — orange (explicit empty parent), NAME = registration.
        $class = SageHorseMapper::map($horse);
        $this->assertSame('FHH00001', $class['id']);
        $this->assertSame('ABJ 1034', $class['name']);
        $this->assertSame('', $class['parentid']);   // forced top-level → orange
        $this->assertSame('Volvo FH 2020', $class['description']);

        // PROJECT — SUB - TRUCKS, child of the transporter project.
        $project = SageHorseMapper::mapProject($horse, 'TRANS-2');
        $this->assertSame('FHH00001', $project['id']);
        $this->assertSame('ABJ 1034', $project['name']);
        $this->assertSame('TRANS-2', $project['parentid']);
        $this->assertSame('SUB - TRUCKS', $project['projecttype']);
        $this->assertSame('Contract', $project['category']);
        $this->assertSame('E100', $project['locationid']);
        $this->assertSame('D2-1', $project['departmentid']);
    }

    /** @test */
    public function transporter_project_is_top_level_subcontractor()
    {
        $t = new Transporter(['name' => 'Fahrenheit']);
        $t->status = 1;

        $project = SageTransporterMapper::mapProject($t);
        $this->assertSame('Fahrenheit', $project['name']);
        $this->assertSame('SUBCONTRACTOR', $project['projecttype']);
        $this->assertSame('Contract', $project['category']);
        $this->assertSame('E100', $project['locationid']);
        $this->assertArrayNotHasKey('parentid', $project); // top-level
    }

    /** @test */
    public function trailer_class_is_green_under_transporter()
    {
        $trailer = new Trailer(['trailer_number' => 'FHT00001', 'registration_number' => 'ABJ 8324']);
        $trailer->status = 1;

        $payload = SageTrailerMapper::map($trailer, 'TRANS-2');
        $this->assertSame('FHT00001', $payload['id']);
        $this->assertSame('ABJ 8324', $payload['name']);
        $this->assertSame('TRANS-2', $payload['parentid']);  // parented → green
    }

    /** @test */
    public function trip_project_uses_manifest_route_parent_class_and_formatted_description()
    {
        $trip = new Trip();
        $trip->manifest_number = '001/01032025';
        $trip->trip_status = 'Delivered';
        $trip->setRelation('fromDestination', $this->destination('Durban'));
        $trip->setRelation('toDestination', $this->destination('Lubumbashi'));

        $payload = SageTripMapper::map($trip, 'FHH00001', 'FHH00001', ['DKZ631L', 'FST623L'], 'FHC00022');

        $this->assertSame('001/01032025', $payload['id']);            // PROJECTID = manifest
        $this->assertSame('DURBAN TO LUBUMBASHI', $payload['name']);   // route, caps
        $this->assertSame('FHH00001', $payload['parentid']);          // horse project
        $this->assertSame('FHH00001', $payload['classid']);           // horse class
        $this->assertSame('TRIPS', $payload['projecttype']);
        $this->assertSame('FHC00022', $payload['customerid']);
        $this->assertSame('E100', $payload['locationid']);
        $this->assertSame('D2-1', $payload['departmentid']);
        $this->assertStringContainsString('Manifest: 001/01032025', $payload['description']);
        $this->assertStringContainsString('Trailers: DKZ631L -FST623L', $payload['description']);
        $this->assertStringContainsString('From: DURBAN Destination: LUBUMBASHI', $payload['description']);
    }
}
