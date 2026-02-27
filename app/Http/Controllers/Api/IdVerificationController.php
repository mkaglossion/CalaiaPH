<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\IdVerificationService;

class IdVerificationController extends Controller
{
    protected $verificationService;

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

        try {
            // Store temporary image
            $tempPath = $request->file('id_image')->store('temp_id_verification', 'public');
            $fullPath = Storage::disk('public')->path($tempPath);

            // Perform verification
            $result = $this->verificationService->verifyIdImage(
                $tempPath,
                $request->id_number,
                $request->id_type
            );

            // Clean up temp file
            Storage::disk('public')->delete($tempPath);

            // Log verification attempt
            Log::info('ID verification attempt', [
                'id_number' => $request->id_number,
                'id_type' => $request->id_type,
                'status' => $result['status'],
                'confidence_score' => $result['confidence_score'],
                'is_verified' => $result['is_verified'],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('ID verification API error', [
                'error' => $e->getMessage(),
                'id_number' => $request->id_number,
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Verification failed: ' . $e->getMessage(),
                'is_verified' => false,
                'requires_manual_review' => true,
            ], 500);
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

        try {
            // Store temporary image
            $tempPath = $request->file('id_image')->store('temp_id_verification', 'public');

            // Quick validation
            $result = $this->verificationService->quickValidate($tempPath);

            // Clean up
            Storage::disk('public')->delete($tempPath);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Check failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify base64 image (from camera capture)
     */
    public function verifyBase64(Request $request)
    {
        $request->validate([
            'id_image_base64' => 'required|string',
            'id_number' => 'required|string|min:6',
            'id_type' => 'required|in:senior,pwd',
        ]);

        try {
            // Decode base64
            $base64Image = $request->id_image_base64;
            
            // Remove data URI scheme if present
            if (strpos($base64Image, 'data:image') === 0) {
                $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            }

            $imageData = base64_decode($base64Image, true);
            if ($imageData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image data',
                ], 422);
            }

            // Save to temp file
            $tempFilename = 'temp_id_verification/' . uniqid('id_') . '.jpg';
            Storage::disk('public')->put($tempFilename, $imageData);

            // Perform verification
            $result = $this->verificationService->verifyIdImage(
                $tempFilename,
                $request->id_number,
                $request->id_type
            );

            // Clean up
            Storage::disk('public')->delete($tempFilename);

            // Log verification
            Log::info('ID verification attempt (base64)', [
                'id_number' => $request->id_number,
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
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
