<?php

namespace Tests\Feature\Sage;

use App\Models\Destination;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Trip;
use App\Services\Sage\Mappers\SageHorseMapper;
use App\Services\Sage\Mappers\SageTrailerMapper;
use App\Services\Sage\Mappers\SageTripMapper;
use App\Services\Sage\Support\SageFormat;
use Tests\TestCase;

/**
 * Pure mapping tests — model instances only, no database.
 */
class SageMapperTest extends TestCase
{
    /** Build a Destination with a name (the model is guarded). */
    protected function destination(string $name): Destination
    {
        $d = new Destination();
        $d->name = $name;
        return $d;
    }

    /** @test */
    public function sage_format_helpers_behave()
    {
        $this->assertSame('ABJ 1034', SageFormat::id('  abj 1034 '));       // trim+upper, spaces kept
        $this->assertSame('ABC1234', SageFormat::id("ABC#1234!"));           // illegal chars stripped
        $this->assertSame('active', SageFormat::boolStatus(1));
        $this->assertSame('inactive', SageFormat::boolStatus(0));
        $this->assertSame('01/15/2020', SageFormat::date('2020-01-15'));
        $this->assertNull(SageFormat::date(''));
    }

    /** @test */
    public function horse_maps_to_class_with_registration_name_and_parent()
    {
        $horse = new Horse([
            'horse_number' => 'FHH00001', 'registration_number' => 'ABJ 1034',
            'manufacturer' => 'Volvo', 'model' => 'FH', 'year' => '2020',
        ]);
        $horse->status = 1;

        $this->assertSame('FHH00001', SageHorseMapper::classId($horse));
        $this->assertSame('ABJ 1034', SageHorseMapper::registration($horse));

        $payload = SageHorseMapper::map($horse, 'TRANS-2');
        $this->assertSame('FHH00001', $payload['id']);
        $this->assertSame('ABJ 1034', $payload['name']);   // NAME = registration
        $this->assertSame('TRANS-2', $payload['parentid']);
        $this->assertSame('active', $payload['status']);
        $this->assertSame('Volvo FH 2020', $payload['description']);
    }

    /** @test */
    public function trailer_maps_to_class_as_sibling_under_transporter()
    {
        $trailer = new Trailer(['trailer_number' => 'FHT00001', 'registration_number' => 'ABJ 8324']);
        $trailer->status = 1;

        $payload = SageTrailerMapper::map($trailer, 'TRANS-2');
        $this->assertSame('FHT00001', $payload['id']);
        $this->assertSame('ABJ 8324', $payload['name']);
        $this->assertSame('TRANS-2', $payload['parentid']);
    }

    /** @test */
    public function trip_route_name_is_uppercase_origin_to_destination()
    {
        $trip = new Trip();
        $trip->trip_number = 'FPT00627';
        $trip->setRelation('fromDestination', $this->destination('Harare'));
        $trip->setRelation('toDestination', $this->destination('Beira'));

        $this->assertSame('HARARE TO BEIRA', SageTripMapper::routeName($trip));
    }

    /** @test */
    public function trip_route_name_falls_back_to_trip_number_when_locations_missing()
    {
        $trip = new Trip();
        $trip->trip_number = 'FPT00627';
        $trip->setRelation('fromDestination', null);
        $trip->setRelation('toDestination', null);
        $trip->setRelation('loading_point', null);
        $trip->setRelation('offloading_point', null);

        $this->assertSame('FPT00627', SageTripMapper::routeName($trip));
    }

    /** @test */
    public function trip_maps_customer_and_horse_class_and_category()
    {
        config()->set('sageintacct.project.category', 'Contract');

        $trip = new Trip();
        $trip->trip_number = 'FPT00627';
        $trip->trip_status = 'Delivered';
        $trip->setRelation('fromDestination', $this->destination('Harare'));
        $trip->setRelation('toDestination', $this->destination('Beira'));

        $payload = SageTripMapper::map($trip, 'FHC00022', 'FHH00001', ['ABJ 8324']);

        $this->assertSame('FPT00627', $payload['id']);
        $this->assertSame('HARARE TO BEIRA', $payload['name']);
        $this->assertSame('Contract', $payload['category']);
        $this->assertSame('FHC00022', $payload['customerid']);
        $this->assertSame('FHH00001', $payload['classid']);
        $this->assertSame('active', $payload['status']);
    }
}
