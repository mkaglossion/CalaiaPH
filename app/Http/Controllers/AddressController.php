<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerAddressRequest;
use App\Models\Barangay;
use App\Models\City;
use App\Models\CustomerAddress;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    /**
     * GET /api/address/provinces/{region}
     * Load provinces for a selected region. NCR returns empty list by design.
     */
    public function provinces(Region $region): JsonResponse
    {
        if ($region->isNcr()) {
            return response()->json([]);
        }

        return response()->json(
            $region->provinces()
                ->orderBy('name')
                ->get(['id', 'name', 'psgc_code'])
        );
    }

    /**
     * GET /api/address/cities/{province}
     * Load cities/municipalities under a province.
     */
    public function cities(Province $province): JsonResponse
    {
        return response()->json(
            $province->cities()
                ->orderBy('name')
                ->get(['id', 'name', 'psgc_code', 'type'])
        );
    }

    /**
     * GET /api/address/cities-by-region/{region}
     * Used for NCR where province level is not applicable.
     */
    public function citiesByRegion(Region $region): JsonResponse
    {
        return response()->json(
            City::query()
                ->where('region_id', $region->id)
                ->when($region->isNcr(), fn ($q) => $q->whereNull('province_id'))
                ->orderBy('name')
                ->get(['id', 'name', 'psgc_code', 'type'])
        );
    }

    /**
     * GET /api/address/barangays/{city}
     */
    public function barangays(City $city): JsonResponse
    {
        return response()->json(
            $city->barangays()
                ->orderBy('name')
                ->get(['id', 'name', 'psgc_code'])
        );
    }

    /**
     * Optional endpoint for storing a normalized customer address record.
     */
    public function store(StoreCustomerAddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $address = CustomerAddress::create($data);
        $address->load(['region', 'province', 'city', 'barangay']);

        return response()->json([
            'message' => 'Address saved successfully.',
            'data' => $address,
        ], 201);
    }
}

