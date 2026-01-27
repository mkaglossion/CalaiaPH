<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class StaycationController extends Controller
{
    public function index()
    {
        $staycations = Staycation::latest()->get();
        return view('admin.staycations', compact('staycations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name',
            'days' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'priceConfigs' => 'nullable|string', // JSON string from frontend
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $capacity = $request->adults + $request->children;

        // Determine type based on name
        $name = strtolower($request->name);
        $type = 'glamping'; // default
        if (str_contains($name, 'byot') || str_contains($name, 'car camping') || str_contains($name, 'bring your own') || str_contains($name, 'dome') || str_contains($name, 'bell')) {
            $type = 'camping';
        }

        // Handle price configs
        $priceConfigs = [];
        if ($request->has('priceConfigs')) {
            $decodedConfigs = is_string($request->priceConfigs) ? json_decode($request->priceConfigs, true) : $request->priceConfigs;

            if (is_array($decodedConfigs)) {
                foreach ($decodedConfigs as $config) {
                    $priceConfig = [];

                    // Handle rates
                    if (isset($config['rates'])) {
                        // Config already has rates structure
                        $priceConfig['rates'] = [
                            'nonPeak' => (float)($config['rates']['nonPeak'] ?? 0),
                            'peak' => (float)($config['rates']['peak'] ?? 0),
                            'superPeak' => (float)($config['rates']['superPeak'] ?? 0),
                        ];
                    } elseif (isset($config['nonPeak']) || isset($config['peak']) || isset($config['superPeak'])) {
                        // Legacy format
                        $priceConfig['rates'] = [
                            'nonPeak' => (float)($config['nonPeak'] ?? 0),
                            'peak' => (float)($config['peak'] ?? 0),
                            'superPeak' => (float)($config['superPeak'] ?? 0),
                        ];
                    }

                    // Handle other config properties
                    if (isset($config['name'])) {
                        $priceConfig['name'] = $config['name'];
                    }
                    if (isset($config['type'])) {
                        $priceConfig['type'] = $config['type'];
                    }
                    if (isset($config['days']) && is_array($config['days'])) {
                        $priceConfig['days'] = $config['days'];
                    }
                    if (isset($config['dates'])) {
                        $priceConfig['dates'] = $config['dates'];
                    }
                    if (isset($config['multipleDates'])) {
                        $priceConfig['multipleDates'] = $config['multipleDates'];
                    }

                    if (!empty($priceConfig)) {
                        $priceConfigs[] = $priceConfig;
                    }
                }
            }
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $imagePath = $uploadResult['secure_url'];
        }

        $staycationData = [
            'name' => $request->name,
            'days' => $request->days,
            'type' => $type,
            'quantity' => $request->quantity,
            'capacity' => $capacity,
            'adults' => $request->adults,
            'children' => $request->children,
            'image' => $imagePath,
            'price' => 0,
        ];

        if (!empty($priceConfigs)) {
            $staycationData['price_configs'] = $priceConfigs;
        }

        $staycation = Staycation::create($staycationData);

        return response()->json([
            'message' => 'Staycation added successfully.',
            'staycation' => $staycation->toArray()
        ]);

    }

    public function show(Staycation $staycation)
    {
        return response()->json($staycation);
    }

    public function update(Request $request, Staycation $staycation_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name,' . $staycation_list->id,
            'days' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'priceConfigs' => 'nullable|string', // JSON string from frontend
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_image' => 'nullable|in:1',
        ]);

        $capacity = $request->adults + $request->children;

        // Determine type based on name
        $name = strtolower($request->name);
        $type = 'glamping'; // default
        if (str_contains($name, 'byot') || str_contains($name, 'car camping') || str_contains($name, 'bring your own') || str_contains($name, 'dome') || str_contains($name, 'bell')) {
            $type = 'camping';
        }

        // Handle price configs
        $priceConfigs = [];
        if ($request->has('priceConfigs')) {
            $decodedConfigs = is_string($request->priceConfigs) ? json_decode($request->priceConfigs, true) : $request->priceConfigs;

            if (is_array($decodedConfigs)) {
                foreach ($decodedConfigs as $config) {
                    $priceConfig = [];

                    // Handle rates
                    if (isset($config['rates'])) {
                        // Config already has rates structure
                        $priceConfig['rates'] = [
                            'nonPeak' => (float)($config['rates']['nonPeak'] ?? 0),
                            'peak' => (float)($config['rates']['peak'] ?? 0),
                            'superPeak' => (float)($config['rates']['superPeak'] ?? 0),
                        ];
                    } elseif (isset($config['nonPeak']) || isset($config['peak']) || isset($config['superPeak'])) {
                        // Legacy format
                        $priceConfig['rates'] = [
                            'nonPeak' => (float)($config['nonPeak'] ?? 0),
                            'peak' => (float)($config['peak'] ?? 0),
                            'superPeak' => (float)($config['superPeak'] ?? 0),
                        ];
                    }

                    // Handle other config properties
                    if (isset($config['name'])) {
                        $priceConfig['name'] = $config['name'];
                    }
                    if (isset($config['type'])) {
                        $priceConfig['type'] = $config['type'];
                    }
                    if (isset($config['days']) && is_array($config['days'])) {
                        $priceConfig['days'] = $config['days'];
                    }
                    if (isset($config['dates'])) {
                        $priceConfig['dates'] = $config['dates'];
                    }
                    if (isset($config['multipleDates'])) {
                        $priceConfig['multipleDates'] = $config['multipleDates'];
                    }

                    if (!empty($priceConfig)) {
                        $priceConfigs[] = $priceConfig;
                    }
                }
            }
        }

        // Handle image upload
        $updateData = [
            'name' => $request->name,
            'days' => $request->days,
            'type' => $type,
            'quantity' => $request->quantity,
            'capacity' => $capacity,
            'adults' => $request->adults,
            'children' => $request->children,
            'price' => 0,
        ];

        if (!empty($priceConfigs)) {
            $updateData['price_configs'] = $priceConfigs;
        }

        if ($request->has('remove_image') && $request->remove_image == '1') {
            // Clear image from database
            // Note: Cloudinary asset will remain but won't be referenced
            $updateData['image'] = null;
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $updateData['image'] = $uploadResult['secure_url'];
        }

        $staycation_list->update($updateData);

        // Return updated data for table refresh
        $updatedStaycation = $staycation_list->fresh();
        $staycationData = $updatedStaycation->toArray();

        // Ensure price_configs is properly included
        $staycationData['price_configs'] = $updatedStaycation->price_configs;

        return response()->json([
            'message' => 'Staycation updated successfully.',
            'staycation' => $staycationData
        ]);
    }

    public function destroy(Staycation $staycation_list)
    {
        $staycation_list->delete();
        return redirect()->back()->with('success', 'Staycation deleted successfully!');
    }
}
