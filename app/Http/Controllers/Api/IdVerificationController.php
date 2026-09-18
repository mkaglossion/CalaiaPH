<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\IdVerificationService;

class IdVerificationController extends Controller
{
    protected IdVerificationService $verificationService;

    public function __construct(IdVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Verify ID image during upload (real-time verification)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'id_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_number' => 'required|string|min:6',
            'id_type' => 'required|in:senior,pwd',
        ]);

        $tempPath = null;

        try {
            // Store temporary image
            $tempPath = $request->file('id_image')->store('temp_id_verification', 'local');

            // Perform verification
            $result = $this->verificationService->verifyIdImage(
                $tempPath,
                $request->id_number,
                $request->id_type
            );

            // Log verification attempt
            Log::info('ID verification attempt', [
                'id_type' => $request->id_type,
                'status' => $result['status'],
                'confidence_score' => $result['confidence_score'],
                'is_verified' => $result['is_verified'],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('ID verification API error', [
                'error' => $e->getMessage(),
                'id_type' => $request->id_type,
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Verification is temporarily unavailable. Please try again.',
                'is_verified' => false,
                'requires_manual_review' => true,
            ], 500);
        } finally {
            if ($tempPath) {
                Storage::disk('local')->delete($tempPath);
            }
        }
    }

    /**
     * Quick quality check before full verification
     */
    public function quickCheck(Request $request)
    {
        $request->validate([
            'id_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $tempPath = null;

        try {
            // Store temporary image
            $tempPath = $request->file('id_image')->store('temp_id_verification', 'local');

            // Quick validation
            $result = $this->verificationService->quickValidate($tempPath);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('ID verification quick check error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Image check is temporarily unavailable. Please try again.',
            ], 500);
        } finally {
            if ($tempPath) {
                Storage::disk('local')->delete($tempPath);
            }
        }
    }

    /**
     * Verify base64 image (from camera capture)
     */
    public function verifyBase64(Request $request)
    {
        $request->validate([
            'id_image_base64' => 'required|string|max:3000000',
            'id_number' => 'required|string|min:6',
            'id_type' => 'required|in:senior,pwd',
        ]);

        $tempFilename = null;

        try {
            // Decode base64
            $base64Image = $request->id_image_base64;
            
            // Remove data URI scheme if present
            if (strpos($base64Image, 'data:image') === 0) {
                $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            }

            $imageData = base64_decode($base64Image, true);
            $imageInfo = $imageData === false ? false : @getimagesizefromstring($imageData);

            if ($imageData === false
                || strlen($imageData) > 2 * 1024 * 1024
                || $imageInfo === false
                || ! in_array($imageInfo['mime'] ?? null, ['image/jpeg', 'image/png'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image data',
                ], 422);
            }

            // Save to temp file
            $tempFilename = 'temp_id_verification/' . uniqid('id_') . '.jpg';
            Storage::disk('local')->put($tempFilename, $imageData);

            // Perform verification
            $result = $this->verificationService->verifyIdImage(
                $tempFilename,
                $request->id_number,
                $request->id_type
            );

            // Log verification
            Log::info('ID verification attempt (base64)', [
                'id_type' => $request->id_type,
                'status' => $result['status'],
                'confidence_score' => $result['confidence_score'],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('ID verification base64 API error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Verification is temporarily unavailable. Please try again.',
            ], 500);
        } finally {
            if ($tempFilename) {
                Storage::disk('local')->delete($tempFilename);
            }
        }
    }
}
