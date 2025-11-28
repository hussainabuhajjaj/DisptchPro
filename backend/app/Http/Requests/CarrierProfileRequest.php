<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarrierProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carrierInfo' => ['required', 'array'],
            'carrierInfo.companyName' => ['required', 'string', 'max:255'],
            'carrierInfo.dba' => ['nullable', 'string', 'max:255'],
            'carrierInfo.physicalAddress' => ['required', 'string', 'max:255'],
            'carrierInfo.physicalCity' => ['required', 'string', 'max:255'],
            'carrierInfo.physicalState' => ['required', 'string', 'max:255'],
            'carrierInfo.physicalZip' => ['required', 'string', 'max:50'],
            'carrierInfo.mailingAddress' => ['nullable', 'string', 'max:255'],
            'carrierInfo.mailingCity' => ['nullable', 'string', 'max:255'],
            'carrierInfo.mailingState' => ['nullable', 'string', 'max:255'],
            'carrierInfo.mailingZip' => ['nullable', 'string', 'max:50'],
            'carrierInfo.mainContact' => ['required', 'string', 'max:255'],
            'carrierInfo.email' => ['required', 'email', 'max:255'],
            'carrierInfo.officePhone' => ['required', 'string', 'max:50'],
            'carrierInfo.fax' => ['nullable', 'string', 'max:50'],
            'carrierInfo.cellPhone' => ['required', 'string', 'max:50'],
            'carrierInfo.emergencyContact' => ['required', 'string', 'max:255'],
            'carrierInfo.emergencyPhone' => ['required', 'string', 'max:50'],
            'carrierInfo.mcNumber' => ['required', 'string', 'max:50'],
            'carrierInfo.dotNumber' => ['required', 'string', 'max:50'],
            'carrierInfo.einNumber' => ['required', 'string', 'max:50'],
            'carrierInfo.scacCode' => ['nullable', 'string', 'max:50'],
            'carrierInfo.twicCertified' => ['nullable', 'string', 'max:50'],
            'carrierInfo.hazmatCertified' => ['nullable', 'string', 'max:50'],

            'equipmentInfo' => ['nullable', 'array'],
            'equipmentInfo.numTrucks' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.companyDrivers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.ownerOperators' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.teamDrivers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.numTrailers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.vanTrailers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.reeferTrailers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.flatbedTrailers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.tankerTrailers' => ['nullable', 'string', 'max:50'],
            'equipmentInfo.otherTrailerTypes' => ['nullable', 'string', 'max:255'],
            'equipmentInfo.vanSizes' => ['nullable', 'string', 'max:255'],
            'equipmentInfo.reeferSizes' => ['nullable', 'string', 'max:255'],
            'equipmentInfo.flatbedSizes' => ['nullable', 'string', 'max:255'],
            'equipmentInfo.tankerSizes' => ['nullable', 'string', 'max:255'],
            'equipmentInfo.tractors' => ['nullable', 'array'],
            'equipmentInfo.tractors.*.year' => ['required_with:equipmentInfo.tractors', 'string', 'max:50'],
            'equipmentInfo.tractors.*.makeModel' => ['required_with:equipmentInfo.tractors', 'string', 'max:100'],
            'equipmentInfo.tractors.*.truckNum' => ['required_with:equipmentInfo.tractors', 'string', 'max:50'],
            'equipmentInfo.tractors.*.vin' => ['required_with:equipmentInfo.tractors', 'string', 'max:100'],
            'equipmentInfo.trailers' => ['nullable', 'array'],
            'equipmentInfo.trailers.*.year' => ['required_with:equipmentInfo.trailers', 'string', 'max:50'],
            'equipmentInfo.trailers.*.makeModel' => ['required_with:equipmentInfo.trailers', 'string', 'max:100'],
            'equipmentInfo.trailers.*.trailerNum' => ['required_with:equipmentInfo.trailers', 'string', 'max:50'],
            'equipmentInfo.trailers.*.vin' => ['required_with:equipmentInfo.trailers', 'string', 'max:100'],
            'equipmentInfo.drivers' => ['nullable', 'array'],
            'equipmentInfo.drivers.*.truckNum' => ['required_with:equipmentInfo.drivers', 'string', 'max:50'],
            'equipmentInfo.drivers.*.trailerNum' => ['required_with:equipmentInfo.drivers', 'string', 'max:50'],
            'equipmentInfo.drivers.*.trailerType' => ['required_with:equipmentInfo.drivers', 'string', 'max:100'],
            'equipmentInfo.drivers.*.maxWeight' => ['required_with:equipmentInfo.drivers', 'string', 'max:50'],
            'equipmentInfo.drivers.*.driverName' => ['required_with:equipmentInfo.drivers', 'string', 'max:255'],
            'equipmentInfo.drivers.*.driverCell' => ['required_with:equipmentInfo.drivers', 'string', 'max:50'],
            'equipmentInfo.driversCanMakeDecisions' => ['nullable', 'in:Yes,No'],
            'equipmentInfo.driversNeedCopy' => ['nullable', 'in:Yes,No'],
            'equipmentInfo.equipmentDescription' => ['nullable', 'string'],
            'equipmentInfo.trailerMix' => ['nullable', 'array'],
            'equipmentInfo.trailerMix.*.type' => ['required_with:equipmentInfo.trailerMix', 'string', 'max:100'],
            'equipmentInfo.trailerMix.*.count' => ['required_with:equipmentInfo.trailerMix', 'string', 'max:50'],

            'operationInfo' => ['nullable', 'array'],
            'operationInfo.states' => ['nullable', 'array'],
            'operationInfo.states.*' => ['string', 'max:100'],
            'operationInfo.canadaProvinces' => ['nullable', 'string', 'max:255'],
            'operationInfo.mexico' => ['nullable', 'string', 'max:255'],
            'operationInfo.minRatePerMile' => ['nullable', 'string', 'max:50'],
            'operationInfo.maxPicks' => ['nullable', 'string', 'max:50'],
            'operationInfo.maxDrops' => ['nullable', 'string', 'max:50'],
            'operationInfo.perPickDrop' => ['nullable', 'string', 'max:50'],
            'operationInfo.driverTouch' => ['nullable', 'in:Yes,No'],
            'operationInfo.driverTouchComments' => ['nullable', 'string'],

            'factoringInfo' => ['nullable', 'array'],
            'factoringInfo.factoringCompany' => ['nullable', 'string', 'max:255'],
            'factoringInfo.mainContact' => ['nullable', 'string', 'max:255'],
            'factoringInfo.phone' => ['nullable', 'string', 'max:50'],
            'factoringInfo.fax' => ['nullable', 'string', 'max:50'],
            'factoringInfo.websiteUrl' => ['nullable', 'string', 'max:255'],
            'factoringInfo.address' => ['nullable', 'string', 'max:255'],
            'factoringInfo.city' => ['nullable', 'string', 'max:255'],
            'factoringInfo.state' => ['nullable', 'string', 'max:255'],
            'factoringInfo.zip' => ['nullable', 'string', 'max:50'],

            'insuranceInfo' => ['nullable', 'array'],
            'insuranceInfo.insuranceAgency' => ['nullable', 'string', 'max:255'],
            'insuranceInfo.mainContact' => ['nullable', 'string', 'max:255'],
            'insuranceInfo.phone' => ['nullable', 'string', 'max:50'],
            'insuranceInfo.fax' => ['nullable', 'string', 'max:50'],
            'insuranceInfo.email' => ['nullable', 'email', 'max:255'],
            'insuranceInfo.address' => ['nullable', 'string', 'max:255'],
            'insuranceInfo.city' => ['nullable', 'string', 'max:255'],
            'insuranceInfo.state' => ['nullable', 'string', 'max:255'],
            'insuranceInfo.zip' => ['nullable', 'string', 'max:50'],
            'insuranceInfo.companyDescription' => ['nullable', 'string'],
        ];
    }
}
