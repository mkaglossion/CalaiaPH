<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use App\Models\PeakPeriod;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class StaycationController extends Controller
{
    public function index()
    {
        $staycations = Staycation::latest()->get();
        
        // Get current season
        $today = now()->format('Y-m-d');
        $currentSeasonPeriod = PeakPeriod::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with('peakType')
            ->orderBy(\App\Models\PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'), 'desc')
            ->first();

        $currentSeason = $currentSeasonPeriod ? $currentSeasonPeriod->peakType->name : 'Non-Peak';
        
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];
        $currentSeasonKey = $seasonKeyMap[$currentSeason] ?? 'nonPeak';
        
        return view('admin.staycations', compact('staycations', 'currentSeason', 'currentSeasonKey'));
    }

    public function store(Request $request)
    {
        Log::info('Store request started');
        
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name',
            'days' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'priceConfigs' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
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
            try {
                $file = $request->file('image');
                $filePath = $file->getRealPath();
                
                if (!file_exists($filePath)) {
                    $filePath = $file->getPathname();
                }
                
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                    'resource_type' => 'auto'
                ]);
                
                // Extract secure_url from ApiResponse object
                if (isset($uploadResult['secure_url'])) {
                    $imagePath = $uploadResult['secure_url'];
                } elseif (property_exists($uploadResult, 'secure_url')) {
                    $imagePath = $uploadResult->secure_url;
                } else {
                    $asArray = (array)$uploadResult;
                    $imagePath = $asArray['secure_url'] ?? null;
                }
                
                if (empty($imagePath)) {
                    throw new \Exception('Could not extract secure_url from Cloudinary response');
                }
            } catch (\Exception $e) {
                Log::error('Image upload failed:', ['message' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                    'errors' => ['image' => ['Failed to upload image to Cloudinary: ' . $e->getMessage()]]
                ], 422);
            }
        }

        $staycationData = [
            'name' => $request->name,
            'days' => $request->days,
            'type' => $type,
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
            'id' => $staycation->id,
            'staycation' => [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'days' => $staycation->days,
                'capacity' => $staycation->capacity,
                'adults' => $staycation->adults,
                'children' => $staycation->children,
                'image' => $staycation->image,
                'price_configs' => $staycation->price_configs,
                'price' => $staycation->price,
                'type' => $staycation->type,
                'created_at' => $staycation->created_at
            ]
        ]);
    }

    public function show(Staycation $staycation_list)
    {
        return response()->json($staycation_list);
    }

    public function update(Request $request, Staycation $staycation_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name,' . $staycation_list->id,
            'days' => 'required|integer|min:1',
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
            try {
                $file = $request->file('image');
                $filePath = $file->getRealPath();
                
                if (!file_exists($filePath)) {
                    $filePath = $file->getPathname();
                }
                
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                    'resource_type' => 'auto'
                ]);
                
                // Extract secure_url from ApiResponse object
                $imageUrl = null;
                if (isset($uploadResult['secure_url'])) {
                    $imageUrl = $uploadResult['secure_url'];
                } elseif (property_exists($uploadResult, 'secure_url')) {
                    $imageUrl = $uploadResult->secure_url;
                } else {
                    $asArray = (array)$uploadResult;
                    $imageUrl = $asArray['secure_url'] ?? null;
                }
                
                if (empty($imageUrl)) {
                    throw new \Exception('Could not extract secure_url from Cloudinary');
                }
                
                $updateData['image'] = $imageUrl;
            } catch (\Exception $e) {
                Log::error('Image update failed:', ['message' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                    'errors' => ['image' => ['Failed to upload image to Cloudinary: ' . $e->getMessage()]]
                ], 422);
            }
        } else {
            // If no new image uploaded, keep existing image
            $updateData['image'] = $staycation_list->image;
        }

        $staycation_list->update($updateData);

        // Return updated data for table refresh
        $updatedStaycation = $staycation_list->fresh();

        return response()->json([
            'message' => 'Staycation updated successfully.',
            'staycation' => [
                'id' => $updatedStaycation->id,
                'name' => $updatedStaycation->name,
                'days' => $updatedStaycation->days,
                'capacity' => $updatedStaycation->capacity,
                'adults' => $updatedStaycation->adults,
                'children' => $updatedStaycation->children,
                'image' => $updatedStaycation->image,
                'price_configs' => $updatedStaycation->price_configs,
                'price' => $updatedStaycation->price,
                'type' => $updatedStaycation->type,
                'created_at' => $updatedStaycation->created_at,
                'updated_at' => $updatedStaycation->updated_at
            ]
        ]);
    }

    public function destroy(Staycation $staycation_list)
    {
        $staycation_list->delete();
        return redirect()->back()->with('success', 'Staycation deleted successfully!');
    }
}
