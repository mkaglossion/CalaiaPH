<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staycation;
use App\Models\StaycationText;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StaycationTextController extends Controller
{
    public function store(Request $request)
    {
        $section = $this->normalizeSection($request->input('section'));
        $isVrSection = $this->isVrSection($section) || $request->hasFile('vr_images');

        if ($isVrSection) {
            $validated = $request->validate([
                'staycation_id' => 'required|exists:staycations,id',
                'section' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'badge_label' => 'nullable|string|max:255',
                'room_type' => 'nullable|in:glamping,camping',
                'description' => 'nullable|string',
                'vr_images' => 'required|array|min:1',
                'vr_images.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:20480',
            ]);

            $text = $this->saveVrSection($request, $validated);
        } else {
            $validated = $request->validate([
                'staycation_id' => 'required|exists:staycations,id',
                'section' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
            ]);

            $text = StaycationText::create([
                'staycation_id' => $validated['staycation_id'],
                'section' => $validated['section'] ?? null,
                'title' => $validated['title'] ?? null,
                'content' => trim((string) ($validated['content'] ?? '')),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $isVrSection ? 'VR content added successfully!' : 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', $isVrSection ? 'VR content added successfully!' : 'Text section added!');
    }

    public function update(Request $request, StaycationText $staycationText)
    {
        $section = $this->normalizeSection($request->input('section', $staycationText->section));
        $isVrSection = $this->isVrSection($section) || $request->hasFile('vr_images');

        if ($isVrSection) {
            $validated = $request->validate([
                'staycation_id' => 'required|exists:staycations,id',
                'section' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'badge_label' => 'nullable|string|max:255',
                'room_type' => 'nullable|in:glamping,camping',
                'description' => 'nullable|string',
                'vr_images' => 'sometimes|array|min:1',
                'vr_images.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:20480',
            ]);

            $text = $this->saveVrSection($request, $validated, $staycationText);
        } else {
            $validated = $request->validate([
                'staycation_id' => 'required|exists:staycations,id',
                'section' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
            ]);

            $staycationText->update([
                'staycation_id' => $validated['staycation_id'] ?? $staycationText->staycation_id,
                'section' => $validated['section'] ?? $staycationText->section,
                'title' => $validated['title'] ?? $staycationText->title,
                'content' => trim((string) ($validated['content'] ?? '')),
            ]);
            $text = $staycationText;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $isVrSection ? 'VR content updated successfully!' : 'Text section updated!',
                'text' => $staycationText
            ]);
        }

        return back()->with('success', $isVrSection ? 'VR content updated successfully!' : 'Text section updated!');
    }


    public function destroy(StaycationText $staycationText)
    {
        if ($this->isVrSection($staycationText->section)) {
            $this->destroyVrMedia($staycationText->content);
        }

        $staycationText->delete();
        return back()->with('success', 'Text deleted successfully!');
    }

    private function normalizeSection(?string $section): string
    {
        return strtolower(trim((string) $section));
    }

    private function isVrSection(?string $section): bool
    {
        return $this->normalizeSection($section) === 'vr';
    }

    private function decodeContent(?string $content): array
    {
        if (!is_string($content) || trim($content) === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function saveVrSection(Request $request, array $validated, ?StaycationText $currentText = null): StaycationText
    {
        $staycation = Staycation::findOrFail($validated['staycation_id']);
        $vrSet = $this->loadVrSet($validated['staycation_id']);
        $content = $this->buildVrContentPayload($request, $staycation, $vrSet['content'] ?? []);

        $record = $vrSet['record'] ?? $currentText ?? new StaycationText();
        $record->staycation_id = $validated['staycation_id'];
        $record->section = $validated['section'] ?? 'vr';
        $record->title = $content['title'];
        $record->content = json_encode($content, JSON_UNESCAPED_SLASHES);
        $record->save();

        foreach ($vrSet['duplicates'] as $duplicate) {
            if ((int) $duplicate->id === (int) $record->id) {
                continue;
            }

            $duplicate->delete();
        }

        return $record;
    }

    private function loadVrSet(int $staycationId): array
    {
        $records = StaycationText::where('staycation_id', $staycationId)
            ->where('section', 'vr')
            ->orderBy('id')
            ->get();

        $record = $records->sortByDesc('id')->first();
        $mergedContent = $record ? $this->decodeContent($record->content) : [];
        $mergedContent['images'] = [];
        $seen = [];

        foreach ($records as $item) {
            $decoded = $this->decodeContent($item->content);
            $images = is_array($decoded['images'] ?? null) ? $decoded['images'] : [];

            foreach ($images as $image) {
                if (!is_array($image)) {
                    continue;
                }

                $key = trim((string) ($image['public_id'] ?? $image['src'] ?? ''));
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $mergedContent['images'][] = $image;
            }
        }

        if ($record) {
            $latestContent = $this->decodeContent($record->content);
            $mergedContent = array_merge($latestContent, ['images' => $mergedContent['images']]);
        }

        return [
            'record' => $record,
            'content' => $mergedContent,
            'duplicates' => $records->filter(fn ($item) => !$record || (int) $item->id !== (int) $record->id)->values(),
        ];
    }

    private function buildVrContentPayload(Request $request, Staycation $staycation, array $existingContent = []): array
    {
        $existingImages = array_values(array_filter(
            is_array($existingContent['images'] ?? null) ? $existingContent['images'] : [],
            fn ($image) => is_array($image) && trim((string) ($image['src'] ?? '')) !== ''
        ));

        $newFiles = $request->hasFile('vr_images') ? array_values(array_filter((array) $request->file('vr_images'))) : [];
        $payloadSlides = $this->decodeSlidePayload($request->input('vr_slide_payload'));
        $images = [];

        if (!empty($payloadSlides)) {
            $newFileIndex = 0;
            foreach ($payloadSlides as $index => $slide) {
                $kind = strtolower(trim((string) ($slide['kind'] ?? 'existing')));

                if ($kind === 'new') {
                    $file = $newFiles[$newFileIndex] ?? null;
                    if (!$file) {
                        continue;
                    }

                    $uploadedSlide = $this->uploadVrSlideFile($file, $index + 1);
                    if ($uploadedSlide) {
                        $uploadedSlide['title'] = $this->cleanVrSlideLabel((string) ($slide['title'] ?? $uploadedSlide['title'] ?? ''), $index + 1);
                        $uploadedSlide['desc'] = $this->cleanVrSlideDescription((string) ($slide['desc'] ?? $uploadedSlide['desc'] ?? ''));
                        $images[] = $uploadedSlide;
                    }

                    $newFileIndex++;
                    continue;
                }

                $src = trim((string) ($slide['src'] ?? ''));
                if ($src === '') {
                    continue;
                }

                $images[] = [
                    'src' => \App\Helpers\ImageHelper::src($src),
                    'public_id' => trim((string) ($slide['public_id'] ?? '')),
                    'title' => $this->cleanVrSlideLabel((string) ($slide['title'] ?? ''), $index + 1),
                    'desc' => $this->cleanVrSlideDescription((string) ($slide['desc'] ?? '')),
                    'width' => (int) ($slide['width'] ?? 0),
                    'height' => (int) ($slide['height'] ?? 0),
                    'isPanoramic' => (bool) ($slide['isPanoramic'] ?? false),
                ];
            }

            while ($newFileIndex < count($newFiles)) {
                $uploadedSlide = $this->uploadVrSlideFile($newFiles[$newFileIndex], count($images) + 1);
                if ($uploadedSlide) {
                    $images[] = $uploadedSlide;
                }
                $newFileIndex++;
            }
        } elseif (!empty($newFiles)) {
            $images = array_values(array_merge($existingImages, $this->uploadVrSlides($newFiles)));
        } else {
            $images = $existingImages;
        }

        $removedPublicIds = array_filter(
            array_column($existingImages, 'public_id'),
            fn ($publicId) => $publicId !== '' && !in_array($publicId, array_filter(array_column($images, 'public_id')), true)
        );

        foreach ($removedPublicIds as $publicId) {
            try {
                Cloudinary::uploadApi()->destroy($publicId);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete removed VR slide from Cloudinary', [
                    'public_id' => $publicId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $roomType = $this->normalizeSection($request->input('room_type', $existingContent['roomType'] ?? $existingContent['room_type'] ?? $staycation->type ?? 'glamping'));
        if (!in_array($roomType, ['glamping', 'camping'], true)) {
            $roomType = 'glamping';
        }

        $badgeLabel = trim((string) $request->input('badge_label', $existingContent['badgeLabel'] ?? $existingContent['badge_label'] ?? ''));
        if ($badgeLabel === '') {
            $badgeLabel = $roomType === 'camping' ? 'Camping' : 'Glamping';
        }

        $title = trim((string) $request->input('title', $existingContent['title'] ?? 'VR Preview'));
        if ($title === '') {
            $title = 'VR Preview';
        }

        $description = trim((string) $request->input('description', $existingContent['description'] ?? ''));
        $palette = $this->vrPalette($roomType);

        return [
            'title' => $title,
            'description' => $description,
            'badgeLabel' => $badgeLabel,
            'roomType' => $roomType,
            'badgeStyle' => $palette['badgeStyle'],
            'headerStyle' => $palette['headerStyle'],
            'headerIcon' => $palette['headerIcon'],
            'images' => $images,
        ];
    }

    private function uploadVrSlides(array $files): array
    {
        $slides = [];

        foreach ($files as $index => $file) {
            $uploadedSlide = $this->uploadVrSlideFile($file, $index + 1);
            if ($uploadedSlide) {
                $slides[] = $uploadedSlide;
            }
        }

        return array_values(array_filter($slides, function ($slide) {
            return is_array($slide) && trim((string) ($slide['src'] ?? '')) !== '';
        }));
    }

    private function uploadVrSlideFile($file, int $fallbackIndex): ?array
    {
        if (!$file) {
            return null;
        }

        $uploadResult = Cloudinary::uploadApi()->upload($file->getRealPath(), ['resource_type' => 'auto']);
        $dimensions = @getimagesize($file->getRealPath()) ?: null;
        $width = is_array($dimensions) ? (int) ($dimensions[0] ?? 0) : 0;
        $height = is_array($dimensions) ? (int) ($dimensions[1] ?? 0) : 0;
        $isPanoramic = $width > 0 && $height > 0 ? (($width / max($height, 1)) >= 1.8) : false;

        return [
            'src' => $uploadResult['secure_url'] ?? '',
            'public_id' => $uploadResult['public_id'] ?? '',
            'title' => 'Slide ' . $fallbackIndex,
            'desc' => '',
            'width' => $width,
            'height' => $height,
            'isPanoramic' => $isPanoramic,
        ];
    }

    private function decodeSlidePayload(?string $payload): array
    {
        if (!is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($slide) => is_array($slide)));
    }

    private function cleanVrSlideLabel(string $value, int $fallbackIndex): string
    {
        $label = trim($value);
        if ($label === '' || $this->looksLikeVrUploadSlug($label) || preg_match('/^slide\s+\d+$/i', $label)) {
            return 'Slide ' . $fallbackIndex;
        }

        return $label;
    }

    private function cleanVrSlideDescription(string $value): string
    {
        $desc = trim($value);
        if ($desc === '' || $this->looksLikeVrUploadSlug($desc) || preg_match('/^slide\s+\d+$/i', $desc)) {
            return '';
        }

        return $desc;
    }

    private function looksLikeVrUploadSlug(string $value): bool
    {
        $text = trim($value);
        if ($text === '') {
            return true;
        }

        if (strlen($text) < 12) {
            return false;
        }

        if (preg_match('/\s/', $text)) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9_-]+$/i', $text);
    }

    private function destroyVrMedia($content): void
    {
        $decoded = is_array($content) ? $content : $this->decodeContent($content);
        $images = array_values(array_filter(
            is_array($decoded['images'] ?? null) ? $decoded['images'] : $decoded,
            fn ($image) => is_array($image) && trim((string) ($image['public_id'] ?? '')) !== ''
        ));

        foreach ($images as $image) {
            try {
                Cloudinary::uploadApi()->destroy($image['public_id']);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete VR slide from Cloudinary', [
                    'public_id' => $image['public_id'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function vrPalette(string $roomType): array
    {
        if ($roomType === 'camping') {
            return [
                'badgeStyle' => 'background:rgba(0,132,141,0.10);color:#00848d',
                'headerStyle' => 'background:#00848d;color:white',
                'headerIcon' => 'bi bi-tree-fill',
            ];
        }

        return [
            'badgeStyle' => 'background:rgba(167,94,62,0.10);color:#a75e3e',
            'headerStyle' => 'background:#a75e3e;color:white',
            'headerIcon' => 'bi bi-house-heart-fill',
        ];
    }
}
