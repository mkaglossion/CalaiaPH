<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageContent;
use App\Models\PackageText;
use Illuminate\Http\Request;

class PackageTextController extends Controller
{
    // PackageContent methods (for package-specific content)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        try {
            // Check if this is for PackageText (page-level) or PackageContent (package-specific)
            if (!$request->package_id && $request->post()) {
                // This is for PackageText (hero page content)
                $text = PackageText::where('title', $request->title)->first();
                if ($text) {
                    $text->update($request->only(['content']));
                    return response()->json(['message' => 'Content updated successfully.']);
                } else {
                    PackageText::create($request->only(['title', 'content']));
                    return response()->json(['message' => 'Content added successfully.']);
                }
            } else {
                // This is for PackageContent (package-specific details)
                $text = PackageContent::where('title', $request->title)->first();
                if ($text) {
                    $text->update($request->only(['content', 'package_id']));
                    return response()->json(['message' => 'Content updated successfully.']);
                } else {
                    PackageContent::create($request->only(['title', 'content', 'package_id']));
                    return response()->json(['message' => 'Content added successfully.']);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to save content: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        try {
            // Try to find and update PackageText first
            $text = PackageText::find($id);
            if ($text) {
                $text->update($request->only(['title', 'content']));
                return response()->json(['message' => 'Content updated successfully.']);
            }

            // Otherwise, try PackageContent
            $content = PackageContent::find($id);
            if ($content) {
                $content->update($request->only(['title', 'content']));
                return response()->json(['message' => 'Content updated successfully.']);
            }

            return response()->json(['message' => 'Content not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update content: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Try to delete PackageText first
            $text = PackageText::find($id);
            if ($text) {
                $text->delete();
                return redirect()->back()->with('success', 'Content deleted successfully.');
            }

            // Otherwise, try PackageContent
            $content = PackageContent::find($id);
            if ($content) {
                $content->delete();
                return redirect()->back()->with('success', 'Content deleted successfully.');
            }

            return redirect()->back()->withErrors('Content not found.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to delete: ' . $e->getMessage());
        }
    }
}
