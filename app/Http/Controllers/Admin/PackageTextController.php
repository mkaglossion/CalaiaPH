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
                    $text->refresh();
                    return response()->json([
                        'message' => 'Content updated successfully.',
                        'text' => $this->textPayload($text),
                    ]);
                } else {
                    $text = PackageText::create($request->only(['title', 'content']));
                    return response()->json([
                        'message' => 'Content added successfully.',
                        'text' => $this->textPayload($text),
                    ]);
                }
            } else {
                // This is for PackageContent (package-specific details)
                $text = PackageContent::where('title', $request->title)->first();
                if ($text) {
                    $text->update($request->only(['content', 'package_id']));
                    $text->refresh();
                    return response()->json([
                        'message' => 'Content updated successfully.',
                        'text' => $this->textPayload($text),
                    ]);
                } else {
                    $text = PackageContent::create($request->only(['title', 'content', 'package_id']));
                    return response()->json([
                        'message' => 'Content added successfully.',
                        'text' => $this->textPayload($text),
                    ]);
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
                $text->refresh();
                return response()->json([
                    'message' => 'Content updated successfully.',
                    'text' => $this->textPayload($text),
                ]);
            }

            // Otherwise, try PackageContent
            $content = PackageContent::find($id);
            if ($content) {
                $content->update($request->only(['title', 'content']));
                $content->refresh();
                return response()->json([
                    'message' => 'Content updated successfully.',
                    'text' => $this->textPayload($content),
                ]);
            }

            return response()->json(['message' => 'Content not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update content: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // Try to delete PackageText first
            $text = PackageText::find($id);
            if ($text) {
                $text->delete();
                return $this->destroyResponse($request, $id);
            }

            // Otherwise, try PackageContent
            $content = PackageContent::find($id);
            if ($content) {
                $content->delete();
                return $this->destroyResponse($request, $id);
            }

            return $this->destroyResponse($request, $id, false, 404);
        } catch (\Exception $e) {
            return $this->destroyResponse($request, $id, false, 500, 'Failed to delete: ' . $e->getMessage());
        }
    }

    private function textPayload($record): array
    {
        return [
            'id' => $record->id,
            'title' => $record->title,
            'content' => $record->content,
            'package_id' => $record->package_id ?? null,
            'created_at' => optional($record->created_at)->toISOString(),
            'updated_at' => optional($record->updated_at)->toISOString(),
        ];
    }

    private function destroyResponse(Request $request, int $id, bool $success = true, int $status = 200, ?string $message = null)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => $message ?? ($success ? 'Content deleted successfully.' : 'Content not found.'),
                'id' => $id,
                'success' => $success,
            ], $status);
        }

        if ($success) {
            return redirect()->back()->with('success', 'Content deleted successfully.');
        }

        return $status === 404
            ? redirect()->back()->withErrors('Content not found.')
            : redirect()->back()->withErrors($message ?? 'Failed to delete.');
    }
}
