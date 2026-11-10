<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate SVG string for inline HTML rendering.
     */
    public function generateSvg(string $url, int $size = 200): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($url);
    }

    /**
     * Generate binary PNG data using GD for fast, reliable server-side download.
     */
    public function generatePng(string $url, int $size = 400): string
    {
        $qr = Encoder::encode($url, ErrorCorrectionLevel::M());
        $matrix = $qr->getMatrix();
        $matrixWidth = $matrix->getWidth();
        $matrixHeight = $matrix->getHeight();

        $margin = 2; // Quiet zone in modules
        $totalModules = $matrixWidth + ($margin * 2);
        $moduleSize = max(1, (int) floor($size / $totalModules));
        $actualImageSize = $totalModules * $moduleSize;

        $image = imagecreatetruecolor($actualImageSize, $actualImageSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 20, 20, 20);

        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $matrixHeight; $y++) {
            for ($x = 0; $x < $matrixWidth; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $x1 = ($x + $margin) * $moduleSize;
                    $y1 = ($y + $margin) * $moduleSize;
                    $x2 = $x1 + $moduleSize - 1;
                    $y2 = $y1 + $moduleSize - 1;
                    imagefilledrectangle($image, $x1, $y1, $x2, $y2, $black);
                }
            }
        }

        ob_start();
        imagepng($image, null, 9);
        $pngData = ob_get_clean();
        imagedestroy($image);

        return $pngData;
    }
}
