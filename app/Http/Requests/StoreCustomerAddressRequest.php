<?php

namespace App\Http\Requests;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id' => ['required', 'exists:regions,id'],
            // NCR has no province, so this is nullable and checked in withValidator().
            'province_id' => ['nullable', 'exists:provinces,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'barangay_id' => ['required', 'exists:barangays,id'],
            'street_address' => ['required', 'string', 'max:255'],
            'subdivision' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:10'],
            'landmark' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $region = Region::find($this->input('region_id'));
            $provinceId = $this->input('province_id');
            $city = City::find($this->input('city_id'));
            $barangay = Barangay::find($this->input('barangay_id'));

            if (!$region) {
                return;
            }

            // Best practice: enforce relational integrity beyond simple exists.
            if ($region->isNcr()) {
                if (!empty($provinceId)) {
                    $validator->errors()->add('province_id', 'Province must be empty for NCR addresses.');
                }
            } else {
                if (empty($provinceId)) {
                    $validator->errors()->add('province_id', 'Province is required for non-NCR addresses.');
                }
            }

            if ($city) {
                if ((int) $city->region_id !== (int) $region->id) {
                    $validator->errors()->add('city_id', 'Selected city does not belong to the selected region.');
                }

                if (!$region->isNcr()) {
                    if (empty($provinceId) || (int) $city->province_id !== (int) $provinceId) {
                        $validator->errors()->add('city_id', 'Selected city does not belong to the selected province.');
                    }
                }
            }

            if ($barangay && $city && (int) $barangay->city_id !== (int) $city->id) {
                $validator->errors()->add('barangay_id', 'Selected barangay does not belong to the selected city.');
            }

            if (!empty($provinceId)) {
                $province = Province::find($provinceId);
                if ($province && (int) $province->region_id !== (int) $region->id) {
                    $validator->errors()->add('province_id', 'Selected province does not belong to the selected region.');
                }
            }
        });
    }
}

