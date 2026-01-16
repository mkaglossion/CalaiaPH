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
            'quantity' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'priceConfigs' => 'required|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $capacity = $request->adults + $request->children;

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $imagePath = $uploadResult['secure_url'];
        }

        $staycation = Staycation::create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'capacity' => $capacity,
            'adults' => $request->adults,
            'children' => $request->children,
            'price_configs' => json_decode($request->priceConfigs, true),
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Staycation added successfully.']);

    }

    public function show(Staycation $staycation)
    {
        return response()->json($staycation);
    }

    public function update(Request $request, Staycation $staycation_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name,' . $staycation_list->id,
            'quantity' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'priceConfigs' => 'required|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_image' => 'nullable|in:1',
        ]);

        $capacity = $request->adults + $request->children;

        // Handle image upload
        $updateData = [
            'name' => $request->name,
            'quantity' => $request->quantity,
            'capacity' => $capacity,
            'adults' => $request->adults,
            'children' => $request->children,
            'price_configs' => json_decode($request->priceConfigs, true),
        ];

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

        return response()->json(['message' => 'Staycation updated successfully.']);
    }

    public function destroy(Staycation $staycation_list)
    {
        $staycation_list->delete();
        return redirect()->back()->with('success', 'Staycation deleted successfully!');
    }
}
