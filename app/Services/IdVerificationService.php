<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * ID Verification Service for Senior Citizen and PWD ID Validation
 * 
 * Uses OCR and image analysis to verify ID legitimacy during upload
 */
class IdVerificationService
{
    /**
     * Minimum image quality requirements
     */
    const MIN_IMAGE_WIDTH = 640;
    const MIN_IMAGE_HEIGHT = 480;
    const MIN_IMAGE_SIZE_KB = 50;
    const MAX_IMAGE_SIZE_KB = 2048;

    /**
     * ID verification result
     */
    public function verifyIdImage(string $imagePath, string $idNumber, string $idType): array
    {
        try {
            $fullPath = Storage::disk('local')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return $this->errorResult('ID image file not found');
            }

            // Check image quality first
            $qualityCheck = $this->checkImageQuality($fullPath);
            if (!$qualityCheck['passed']) {
                return $this->errorResult($qualityCheck['message']);
            }

            // Perform OCR to extract text from image
            $ocrResult = $this->performOCR($fullPath);
            
            // Analyze extracted text
            $textAnalysis = $this->analyzeExtractedText($ocrResult['text'], $idNumber, $idType);
            
            // Check for visual ID markers (security features)
            $visualCheck = $this->checkVisualMarkers($fullPath, $idType);
            
            // Calculate confidence score
            $confidenceScore = $this->calculateConfidenceScore($textAnalysis, $visualCheck, $qualityCheck);
            
            // Determine verification status
            $status = $this->determineVerificationStatus($confidenceScore, $textAnalysis);
            
            return [
                'success' => true,
                'status' => $status,
                'confidence_score' => $confidenceScore,
                'is_verified' => $status === 'verified',
                'requires_manual_review' => $status === 'pending_review',
                'extracted_text' => $ocrResult['text'],
                'id_number_match' => $textAnalysis['id_number_match'],
                'id_type_detected' => $textAnalysis['id_type_detected'],
                'quality_score' => $qualityCheck['score'],
                'visual_check_passed' => $visualCheck['passed'],
                'message' => $this->getStatusMessage($status, $textAnalysis),
                'warnings' => $textAnalysis['warnings'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('ID verification failed', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);
            
            return $this->errorResult('Verification is temporarily unavailable. Please try again.');
        }
    }

    /**
     * Check image quality metrics
     */
    private function checkImageQuality(string $imagePath): array
    {
        try {
            $imageInfo = getimagesize($imagePath);
            $fileSize = filesize($imagePath) / 1024; // KB

            if (!$imageInfo) {
                return ['passed' => false, 'message' => 'Invalid image file', 'score' => 0];
            }

            [$width, $height] = $imageInfo;
            
            $issues = [];
            $score = 100;

            // Check dimensions
            if ($width < self::MIN_IMAGE_WIDTH || $height < self::MIN_IMAGE_HEIGHT) {
                $issues[] = "Image resolution too low ({$width}x{$height}). Minimum: " . self::MIN_IMAGE_WIDTH . "x" . self::MIN_IMAGE_HEIGHT;
                $score -= 30;
            }

            // Check file size
            if ($fileSize < self::MIN_IMAGE_SIZE_KB) {
                $issues[] = "Image file size too small ({$fileSize}KB). Minimum: " . self::MIN_IMAGE_SIZE_KB . "KB";
                $score -= 20;
            }
            if ($fileSize > self::MAX_IMAGE_SIZE_KB) {
                $issues[] = "Image file size too large ({$fileSize}KB). Maximum: " . self::MAX_IMAGE_SIZE_KB . "KB";
                $score -= 10;
            }

            // Check aspect ratio (ID cards are typically landscape or portrait)
            $aspectRatio = $width / $height;
            if ($aspectRatio < 0.5 || $aspectRatio > 2.5) {
                $issues[] = "Unusual aspect ratio. ID cards should be rectangular.";
                $score -= 15;
            }

            // Basic blur detection using simple edge detection
            $blurScore = $this->detectBlur($imagePath);
            if ($blurScore < 50) {
                $issues[] = "Image appears blurry or out of focus";
                $score -= 25;
            }

            return [
                'passed' => $score >= 60,
                'score' => max(0, $score),
                'width' => $width,
                'height' => $height,
                'file_size_kb' => $fileSize,
                'aspect_ratio' => $aspectRatio,
                'blur_score' => $blurScore,
                'issues' => $issues,
                'message' => empty($issues) ? 'Image quality acceptable' : implode('; ', $issues),
            ];

        } catch (\Exception $e) {
            return ['passed' => false, 'message' => 'Image quality could not be checked.', 'score' => 0];
        }
    }

    /**
     * Simple blur detection using Laplacian variance
     */
    private function detectBlur(string $imagePath): float
    {
        try {
            // Create image from file
            $image = imagecreatefromstring(file_get_contents($imagePath));
            if (!$image) {
                return 0;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Resize for faster processing
            $smallWidth = 256;
            $smallHeight = intval($height * ($smallWidth / $width));
            $smallImage = imagecreatetruecolor($smallWidth, $smallHeight);
            imagecopyresampled($smallImage, $image, 0, 0, 0, 0, $smallWidth, $smallHeight, $width, $height);

            // Calculate variance of Laplacian (edge detection)
            $laplacian = [];
            for ($y = 1; $y < $smallHeight - 1; $y++) {
                for ($x = 1; $x < $smallWidth - 1; $x++) {
                    // Get pixel values
                    $center = imagecolorat($smallImage, $x, $y) & 0xFF;
                    $top = imagecolorat($smallImage, $x, $y - 1) & 0xFF;
                    $bottom = imagecolorat($smallImage, $x, $y + 1) & 0xFF;
                    $left = imagecolorat($smallImage, $x - 1, $y) & 0xFF;
                    $right = imagecolorat($smallImage, $x + 1, $y) & 0xFF;

                    // Laplacian kernel
                    $laplacianValue = 4 * $center - $top - $bottom - $left - $right;
                    $laplacian[] = $laplacianValue;
                }
            }

            // Calculate variance
            $mean = array_sum($laplacian) / count($laplacian);
            $variance = 0;
            foreach ($laplacian as $value) {
                $variance += pow($value - $mean, 2);
            }
            $variance /= count($laplacian);

            // Cleanup
            imagedestroy($image);
            imagedestroy($smallImage);

            // Normalize score (0-100)
            $score = min(100, max(0, sqrt($variance) / 10));
            
            return $score;

        } catch (\Exception $e) {
            return 50; // Default to medium score on error
        }
    }

    /**
     * Perform OCR on image
     * Uses Tesseract if available, otherwise returns simulated result for demo
     */
    private function performOCR(string $imagePath): array
    {
        // Check if Tesseract is available
        $tesseractPath = $this->getTesseractPath();
        
        if ($tesseractPath && shell_exec("which {$tesseractPath}")) {
            return $this->performTesseractOCR($imagePath, $tesseractPath);
        }

        // Fallback: Use basic text extraction or external API
        return $this->performBasicOCR($imagePath);
    }

    /**
     * Get Tesseract OCR path
     */
    private function getTesseractPath(): ?string
    {
        $possiblePaths = [
            'tesseract',
            '/usr/bin/tesseract',
            '/usr/local/bin/tesseract',
            'C:\Program Files\Tesseract-OCR\tesseract.exe',
            'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
        ];

        foreach ($possiblePaths as $path) {
            if (shell_exec("which " . escapeshellarg($path) . " 2>/dev/null") || file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Perform OCR using Tesseract
     */
    private function performTesseractOCR(string $imagePath, string $tesseractPath): array
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'ocr_');
            
            // Run Tesseract
            $command = escapeshellcmd($tesseractPath) . ' ' . 
                       escapeshellarg($imagePath) . ' ' . 
                       escapeshellarg($tempFile) . ' -l eng+fil 2>&1';
            
            shell_exec($command);
            
            // Read output
            $outputFile = $tempFile . '.txt';
            $text = file_exists($outputFile) ? file_get_contents($outputFile) : '';
            
            // Cleanup
            @unlink($tempFile);
            @unlink($outputFile);
            
            return [
                'text' => $text,
                'method' => 'tesseract',
                'confidence' => $this->estimateOCRConfidence($text),
            ];

        } catch (\Exception $e) {
            return $this->performBasicOCR($imagePath);
        }
    }

    /**
     * Basic OCR fallback using image analysis
     */
    private function performBasicOCR(string $imagePath): array
    {
        // For demo/development without Tesseract
        // In production, integrate with Google Vision API, AWS Textract, or similar
        
        // Simulate OCR by checking if image contains text-like patterns
        $image = imagecreatefromstring(file_get_contents($imagePath));
        
        if (!$image) {
            return ['text' => '', 'method' => 'basic', 'confidence' => 0];
        }

        // Basic text detection by analyzing pixel patterns
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Sample regions where text typically appears on IDs
        $textRegions = [];
        
        // Top region (header)
        $textRegions[] = $this->sampleRegion($image, 0, 0, $width, intval($height * 0.2));
        
        // Middle region (ID number)
        $textRegions[] = $this->sampleRegion($image, intval($width * 0.2), intval($height * 0.4), intval($width * 0.6), intval($height * 0.2));
        
        // Bottom region (name/signature)
        $textRegions[] = $this->sampleRegion($image, 0, intval($height * 0.7), $width, intval($height * 0.3));

        imagedestroy($image);

        // Combine region data
        $hasTextLikePatterns = array_sum($textRegions) > 2;
        
        return [
            'text' => $hasTextLikePatterns ? '[Text detected in image]' : '[No clear text detected]',
            'method' => 'basic',
            'confidence' => $hasTextLikePatterns ? 60 : 30,
            'regions_analyzed' => count($textRegions),
        ];
    }

    /**
     * Sample a region of the image for text-like patterns
     */
    private function sampleRegion(\GdImage $image, int $x, int $y, int $width, int $height): int
    {
        $edgeCount = 0;
        $samplePoints = min(100, $width * $height / 100);
        
        for ($i = 0; $i < $samplePoints; $i++) {
            $sx = $x + rand(0, $width - 1);
            $sy = $y + rand(0, $height - 1);
            
            if ($sx > 0 && $sx < imagesx($image) - 1 && $sy > 0 && $sy < imagesy($image) - 1) {
                $current = imagecolorat($image, $sx, $sy) & 0xFF;
                $right = imagecolorat($image, $sx + 1, $sy) & 0xFF;
                
                if (abs($current - $right) > 30) {
                    $edgeCount++;
                }
            }
        }
        
        return $edgeCount > ($samplePoints * 0.1) ? 1 : 0;
    }

    /**
     * Analyze extracted text for ID validation
     */
    private function analyzeExtractedText(string $text, string $expectedIdNumber, string $expectedType): array
    {
        $text = strtoupper($text);
        $warnings = [];
        
        // Check ID number match
        $cleanExpected = preg_replace('/[^A-Z0-9]/', '', strtoupper($expectedIdNumber));
        $cleanExtracted = preg_replace('/[^A-Z0-9]/', '', $text);
        
        $idNumberMatch = false;
        $matchConfidence = 0;
        
        if (strpos($cleanExtracted, $cleanExpected) !== false) {
            $idNumberMatch = true;
            $matchConfidence = 100;
        } elseif (similar_text($cleanExpected, $this->extractBestIdMatch($cleanExtracted)) > 80) {
            $idNumberMatch = true;
            $matchConfidence = 80;
            $warnings[] = 'ID number partially matched - please verify';
        } else {
            $warnings[] = 'ID number in image does not match entered ID';
        }

        // Detect ID type from text
        $idTypeDetected = 'unknown';
        $seniorKeywords = ['SENIOR', 'CITIZEN', 'OSC', 'SC ID', 'SENIOR CITIZEN'];
        $pwdKeywords = ['PWD', 'PERSON WITH DISABILITY', 'DISABILITY', 'IP', 'MC'];
        
        foreach ($seniorKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $idTypeDetected = 'senior';
                break;
            }
        }
        
        if ($idTypeDetected === 'unknown') {
            foreach ($pwdKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $idTypeDetected = 'pwd';
                    break;
                }
            }
        }

        // Check for required ID elements
        $requiredElements = [
            'republic' => strpos($text, 'REPUBLIC') !== false || strpos($text, 'PHILIPPINES') !== false,
            'id_number_format' => preg_match('/(SC|PWD|IP|MC)[-\s]?\d{4,}/', $text),
            'name_field' => preg_match('/(NAME|LASTNAME|FIRSTNAME|GIVEN)/', $text),
        ];

        $missingElements = array_filter($requiredElements, function($v) { return !$v; });
        
        if (!empty($missingElements)) {
            $warnings[] = 'Some standard ID elements not detected: ' . implode(', ', array_keys($missingElements));
        }

        return [
            'id_number_match' => $idNumberMatch,
            'match_confidence' => $matchConfidence,
            'id_type_detected' => $idTypeDetected,
            'id_type_correct' => $idTypeDetected === $expectedType || $idTypeDetected === 'unknown',
            'required_elements_found' => $requiredElements,
            'missing_elements' => array_keys($missingElements),
            'warnings' => $warnings,
            'text_length' => strlen($text),
        ];
    }

    /**
     * Extract best ID number match from text
     */
    private function extractBestIdMatch(string $text): string
    {
        // Look for patterns like SC-123456 or PWD-123456
        if (preg_match('/(SC|PWD)[-\s]?(\d{6,})/', $text, $matches)) {
            return $matches[1] . $matches[2];
        }
        
        return '';
    }

    /**
     * Check visual markers of legitimate ID
     */
    private function checkVisualMarkers(string $imagePath, string $idType): array
    {
        try {
            $image = imagecreatefromstring(file_get_contents($imagePath));
            if (!$image) {
                return ['passed' => false, 'message' => 'Could not analyze image'];
            }

            $width = imagesx($image);
            $height = imagesy($image);
            
            $checks = [
                'has_photo_area' => $this->checkForPhotoArea($image, $width, $height),
                'has_signature_area' => $this->checkForSignatureArea($image, $width, $height),
                'color_profile_valid' => $this->checkColorProfile($image, $idType),
                'security_pattern_detected' => $this->detectSecurityPattern($image, $width, $height),
            ];

            imagedestroy($image);

            $passedCount = count(array_filter($checks));
            $totalChecks = count($checks);
            
            return [
                'passed' => $passedCount >= 2,
                'score' => ($passedCount / $totalChecks) * 100,
                'checks' => $checks,
                'message' => "Visual checks: {$passedCount}/{$totalChecks} passed",
            ];

        } catch (\Exception $e) {
            return ['passed' => false, 'message' => 'Visual checks could not be completed.'];
        }
    }

    /**
     * Check for photo area (typically top-left or top-right)
     */
    private function checkForPhotoArea(\GdImage $image, int $width, int $height): bool
    {
        // Sample top-left corner for photo-like area
        $photoRegion = [
            'x' => intval($width * 0.05),
            'y' => intval($height * 0.1),
            'w' => intval($width * 0.3),
            'h' => intval($height * 0.4),
        ];

        $variance = $this->calculateRegionVariance($image, $photoRegion);
        
        // Photo areas typically have moderate variance (not too uniform, not too noisy)
        return $variance > 500 && $variance < 5000;
    }

    /**
     * Check for signature area (typically bottom)
     */
    private function checkForSignatureArea(\GdImage $image, int $width, int $height): bool
    {
        $sigRegion = [
            'x' => intval($width * 0.5),
            'y' => intval($height * 0.7),
            'w' => intval($width * 0.45),
            'h' => intval($height * 0.25),
        ];

        $variance = $this->calculateRegionVariance($image, $sigRegion);
        
        // Signature areas have high variance due to handwriting
        return $variance > 1000;
    }

    /**
     * Check color profile matches expected ID type
     */
    private function checkColorProfile(\GdImage $image, string $idType): bool
    {
        // Sample colors from the image
        $width = imagesx($image);
        $height = imagesy($image);
        
        $samples = [];
        for ($i = 0; $i < 100; $i++) {
            $x = rand(0, $width - 1);
            $y = rand(0, $height - 1);
            $rgb = imagecolorat($image, $x, $y);
            $samples[] = [
                'r' => ($rgb >> 16) & 0xFF,
                'g' => ($rgb >> 8) & 0xFF,
                'b' => $rgb & 0xFF,
            ];
        }

        // Calculate average color
        $avgR = array_sum(array_column($samples, 'r')) / count($samples);
        $avgG = array_sum(array_column($samples, 'g')) / count($samples);
        $avgB = array_sum(array_column($samples, 'b')) / count($samples);

        // Senior IDs typically have blue/purple tones
        // PWD IDs typically have yellow/orange tones
        if ($idType === 'senior') {
            return $avgB > $avgR && $avgB > $avgG; // Blue dominant
        } elseif ($idType === 'pwd') {
            return $avgR > 200 && $avgG > 150; // Yellow/orange dominant
        }

        return true;
    }

    /**
     * Detect security patterns (hologram, watermark areas)
     */
    private function detectSecurityPattern(\GdImage $image, int $width, int $height): bool
    {
        // Check for reflective/shiny areas that might indicate holograms
        $centerX = intval($width / 2);
        $centerY = intval($height / 2);
        
        $shinyPixels = 0;
        $sampleSize = 50;
        
        for ($i = 0; $i < $sampleSize; $i++) {
            $x = $centerX + rand(-50, 50);
            $y = $centerY + rand(-50, 50);
            
            if ($x > 0 && $x < $width && $y > 0 && $y < $height) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                // Shiny/reflective areas have high brightness
                $brightness = ($r + $g + $b) / 3;
                if ($brightness > 200) {
                    $shinyPixels++;
                }
            }
        }
        
        // Some shiny pixels indicate possible security features
        return $shinyPixels > ($sampleSize * 0.1);
    }

    /**
     * Calculate variance of a region
     */
    private function calculateRegionVariance(\GdImage $image, array $region): float
    {
        $pixels = [];
        
        for ($y = $region['y']; $y < $region['y'] + $region['h']; $y += 5) {
            for ($x = $region['x']; $x < $region['x'] + $region['w']; $x += 5) {
                if ($x < imagesx($image) && $y < imagesy($image)) {
                    $rgb = imagecolorat($image, $x, $y);
                    $pixels[] = ($rgb >> 16) & 0xFF; // Red channel for simplicity
                }
            }
        }
        
        if (empty($pixels)) {
            return 0;
        }
        
        $mean = array_sum($pixels) / count($pixels);
        $variance = 0;
        
        foreach ($pixels as $pixel) {
            $variance += pow($pixel - $mean, 2);
        }
        
        return $variance / count($pixels);
    }

    /**
     * Calculate overall confidence score
     */
    private function calculateConfidenceScore(array $textAnalysis, array $visualCheck, array $qualityCheck): float
    {
        $scores = [
            'text_match' => $textAnalysis['id_number_match'] ? ($textAnalysis['match_confidence'] * 0.3) : 0,
            'id_type' => $textAnalysis['id_type_correct'] ? 20 : 5,
            'elements' => (count($textAnalysis['missing_elements']) === 0) ? 20 : max(0, 20 - (count($textAnalysis['missing_elements']) * 5)),
            'visual' => $visualCheck['score'] * 0.2,
            'quality' => $qualityCheck['score'] * 0.1,
        ];
        
        return array_sum($scores);
    }

    /**
     * Determine verification status based on confidence score
     * STRICT MODE: Only accept clear, valid IDs
     */
    private function determineVerificationStatus(float $confidenceScore, array $textAnalysis): string
    {
        // Must have ID number match to proceed at all
        if (!$textAnalysis['id_number_match']) {
            return 'rejected';
        }

        // Must detect correct ID type
        if (!$textAnalysis['id_type_correct'] || $textAnalysis['id_type_detected'] === 'unknown') {
            return 'rejected';
        }

        // Must have minimum required elements (Republic/Philippines + ID format + name field)
        $minRequiredElements = 2; // At least 2 of 3 required elements
        $foundElements = count(array_filter($textAnalysis['required_elements_found']));
        if ($foundElements < $minRequiredElements) {
            return 'rejected';
        }

        // Auto-verify only if VERY high confidence and ALL checks pass
        if ($confidenceScore >= 90 && 
            $textAnalysis['id_number_match'] && 
            $textAnalysis['id_type_correct'] &&
            $foundElements >= 3 &&
            empty($textAnalysis['warnings'])) {
            return 'verified';
        }
        
        // Pending review only if good confidence with minor issues
        if ($confidenceScore >= 70 && 
            $textAnalysis['id_number_match'] && 
            $textAnalysis['id_type_correct'] &&
            $foundElements >= 2) {
            return 'pending_review';
        }
        
        // Reject everything else - be strict!
        return 'rejected';
    }


    /**
     * Get human-readable status message
     */
    private function getStatusMessage(string $status, array $textAnalysis): string
    {
        switch ($status) {
            case 'verified':
                return 'ID verified automatically. All checks passed.';
                
            case 'pending_review':
                if (!$textAnalysis['id_number_match']) {
                    return 'ID requires manual review: ID number could not be verified from image.';
                }
                if (!$textAnalysis['id_type_correct']) {
                    return 'ID requires manual review: ID type could not be confirmed.';
                }
                return 'ID requires manual review by admin. Some verification checks were inconclusive.';
                
            case 'rejected':
                return 'ID verification failed. Please upload a clearer image of your valid ID.';
                
            default:
                return 'Unknown verification status.';
        }
    }

    /**
     * Estimate OCR confidence based on text quality
     */
    private function estimateOCRConfidence(string $text): float
    {
        if (empty($text)) {
            return 0;
        }
        
        $length = strlen($text);
        $wordCount = str_word_count($text);
        
        // More text and words generally means better OCR
        $score = min(100, ($length / 10) + ($wordCount * 5));
        
        return min(100, $score);
    }

    /**
     * Create error result
     */
    private function errorResult(string $message): array
    {
        return [
            'success' => false,
            'status' => 'error',
            'is_verified' => false,
            'requires_manual_review' => true,
            'message' => $message,
            'confidence_score' => 0,
        ];
    }

    /**
     * Quick validation without full verification
     * Used for real-time feedback during upload
     */
    public function quickValidate(string $imagePath): array
    {
        try {
            $fullPath = Storage::disk('local')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                return ['valid' => false, 'message' => 'File not found'];
            }

            $qualityCheck = $this->checkImageQuality($fullPath);
            
            return [
                'valid' => $qualityCheck['passed'],
                'quality_score' => $qualityCheck['score'],
                'message' => $qualityCheck['message'],
                'issues' => $qualityCheck['issues'] ?? [],
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Image validation is temporarily unavailable.'];
        }
    }
}
