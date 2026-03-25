<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'title' => 'nullable|string|max:255',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        try {
            if ($request->file('image')) {
                $path = $request->file('image')->store('packages', 'public');
                
                PackageImage::create([
                    'image_path' => '/storage/' . $path,
                    'alt_text' => $request->title,
                    'package_id' => $request->package_id,
                ]);

                return response()->json(['message' => 'Image uploaded successfully.']);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload image: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, PackageImage $packageImage)
    {
        $request->validate([
            'image' => 'nullable|image|max:10240',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            if ($request->file('image')) {
                // Delete old image
                if ($packageImage->image_path) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $packageImage->image_path));
                }
                
                $path = $request->file('image')->store('packages', 'public');
                $packageImage->update([
                    'image_path' => '/storage/' . $path,
                    'alt_text' => $request->title,
                ]);
            } else {
                $packageImage->update(['alt_text' => $request->title]);
            }

            return response()->json(['message' => 'Image updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update image: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PackageImage $packageImage)
    {
        if ($packageImage->image_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $packageImage->image_path));
        }
        
        $packageImage->delete();
        return redirect()->back()->with('success', 'Image deleted successfully.');
    }

    public function getImages($packageId)
    {
        $images = PackageImage::where('package_id', $packageId)->get();
        return response()->json($images);
    }
}
