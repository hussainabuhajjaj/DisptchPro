<?php

namespace Tests\Feature;

use App\Models\CarrierProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrierProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'carrierInfo' => [
                'companyName' => 'Acme Logistics',
                'dba' => 'Acme',
                'physicalAddress' => '123 Main St',
                'physicalCity' => 'Dallas',
                'physicalState' => 'TX',
                'physicalZip' => '75001',
                'mailingAddress' => 'PO Box 1',
                'mailingCity' => 'Dallas',
                'mailingState' => 'TX',
                'mailingZip' => '75002',
                'mainContact' => 'Jane Doe',
                'email' => 'jane@example.com',
                'officePhone' => '555-1111',
                'fax' => '555-2222',
                'cellPhone' => '555-3333',
                'emergencyContact' => 'John Doe',
                'emergencyPhone' => '555-4444',
                'mcNumber' => 'MC123',
                'dotNumber' => 'DOT123',
                'einNumber' => 'EIN123',
                'scacCode' => 'SCAC',
                'twicCertified' => 'Yes',
                'hazmatCertified' => 'No',
            ],
            'equipmentInfo' => [
                'numTrucks' => '5',
                'companyDrivers' => '3',
                'ownerOperators' => '2',
                'teamDrivers' => '1',
                'numTrailers' => '5',
                'vanTrailers' => '3',
                'reeferTrailers' => '2',
                'flatbedTrailers' => '0',
                'tankerTrailers' => '0',
                'otherTrailerTypes' => 'None',
                'vanSizes' => "53'",
                'reeferSizes' => "53'",
                'flatbedSizes' => null,
                'tankerSizes' => null,
                'tractors' => [
                    ['year' => '2020', 'makeModel' => 'Volvo', 'truckNum' => 'T1', 'vin' => 'VIN123'],
                ],
                'trailers' => [
                    ['year' => '2021', 'makeModel' => 'Great Dane', 'trailerNum' => 'TR1', 'vin' => 'VIN456'],
                ],
                'drivers' => [
                    ['truckNum' => 'T1', 'trailerNum' => 'TR1', 'trailerType' => 'Van', 'maxWeight' => '45000', 'driverName' => 'Sam Driver', 'driverCell' => '555-5555'],
                ],
                'driversCanMakeDecisions' => 'Yes',
                'driversNeedCopy' => 'No',
                'equipmentDescription' => 'Good equipment',
            ],
            'operationInfo' => [
                'states' => ['TX', 'OK'],
                'canadaProvinces' => '',
                'mexico' => '',
                'minRatePerMile' => '2.50',
                'maxPicks' => '3',
                'maxDrops' => '2',
                'perPickDrop' => '50',
                'driverTouch' => 'No',
                'driverTouchComments' => '',
            ],
            'factoringInfo' => [
                'factoringCompany' => 'Factor Inc',
                'mainContact' => 'Bob',
                'phone' => '555-6666',
                'fax' => '',
                'websiteUrl' => 'https://factor.example.com',
                'address' => '1 Factor Way',
                'city' => 'Dallas',
                'state' => 'TX',
                'zip' => '75003',
            ],
            'insuranceInfo' => [
                'insuranceAgency' => 'Safe Insurance',
                'mainContact' => 'Alice',
                'phone' => '555-7777',
                'fax' => '',
                'email' => 'alice@safe.com',
                'address' => '2 Safe St',
                'city' => 'Dallas',
                'state' => 'TX',
                'zip' => '75004',
                'companyDescription' => 'Long-term partner',
            ],
        ];
    }

    public function test_it_stores_carrier_profile_with_valid_payload(): void
    {
        $response = $this->postJson('/api/carrier-profile', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'status', 'createdAt']);

        $profile = CarrierProfile::first();
        $this->assertNotNull($profile);
        $this->assertSame('Acme Logistics', $profile->carrier_info['companyName']);
        $this->assertSame('submitted', $profile->status);
    }

    public function test_it_rejects_missing_required_fields(): void
    {
        $response = $this->postJson('/api/carrier-profile', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'carrierInfo',
                'carrierInfo.companyName',
                'carrierInfo.physicalAddress',
                'carrierInfo.physicalCity',
                'carrierInfo.physicalState',
                'carrierInfo.physicalZip',
                'carrierInfo.mainContact',
                'carrierInfo.email',
                'carrierInfo.officePhone',
                'carrierInfo.cellPhone',
                'carrierInfo.emergencyContact',
                'carrierInfo.emergencyPhone',
                'carrierInfo.mcNumber',
                'carrierInfo.dotNumber',
                'carrierInfo.einNumber',
            ]);
    }
}
