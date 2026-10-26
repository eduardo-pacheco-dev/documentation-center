<?php

namespace App\Services\Forms;

use Imagick;
use Throwable;

class ImageThumbnailer
{
    /**
     * Longest edge of a generated thumbnail, in pixels.
     */
    private const MAX_EDGE = 400;

    /**
     * JPEG quality used by the generated thumbnails.
     */
    private const JPEG_QUALITY = 72;

    /**
     * Maximum number of pixels handled by the GD fallback.
     *
     * Larger images are left without a thumbnail so the request never exhausts
     * the PHP memory limit. Imagick, when available, does not have this limit.
     */
    private const GD_MAX_PIXELS = 20_000_000;

    /**
     * Build a compressed JPEG thumbnail of the given image.
     *
     * Returns null when the image cannot be read or converted, so callers can
     * keep the original upload as a safe fallback.
     */
    public function make(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        try {
            if (class_exists(Imagick::class)) {
                return $this->withImagick($path);
            }

            return $this->withGd($path);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Generate the thumbnail with Imagick, preserving the photo orientation.
     */
    private function withImagick(string $path): ?string
    {
        $image = new Imagick($path);

        try {
            $image->autoOrient();
            $image->thumbnailImage(self::MAX_EDGE, self::MAX_EDGE, true);
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(self::JPEG_QUALITY);
            $image->stripImage();

            $contents = $image->getImageBlob();

            return $contents !== '' ? $contents : null;
        } finally {
            $image->clear();
        }
    }

    /**
     * Generate the thumbnail with the GD extension.
     */
    private function withGd(string $path): ?string
    {
        $size = @getimagesize($path);

        if ($size === false) {
            return null;
        }

        [$width, $height] = $size;

        if ($width < 1 || $height < 1 || $width * $height > self::GD_MAX_PIXELS) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));

        if ($source === false) {
            return null;
        }

        $scale = min(1, self::MAX_EDGE / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

        // JPEG has no transparency, so flatten the image on a white background.
        imagefill($thumbnail, 0, 0, (int) imagecolorallocate($thumbnail, 255, 255, 255));
        imagecopyresampled(
            $thumbnail,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );
        imageinterlace($thumbnail, true);

        ob_start();
        imagejpeg($thumbnail, null, self::JPEG_QUALITY);
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($thumbnail);

        return is_string($contents) && $contents !== '' ? $contents : null;
    }
}
