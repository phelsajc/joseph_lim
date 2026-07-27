<?php

namespace App\Services;

class ImageOrientationService
{
    /**
     * @param resource|\GdImage $src
     * @param string|null $filePath
     * @return resource|\GdImage
     */
    public static function applyExifOrientationToGdImage($src, $filePath)
    {
        if (!$filePath || !is_file($filePath) || !function_exists('exif_read_data') || !function_exists('imagerotate')) {
            return $src;
        }

        try {
            $exif = @exif_read_data($filePath);
            $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
            if ($orientation <= 1) {
                return $src;
            }

            return self::applyOrientationValueToGdImage($src, $orientation);
        } catch (\Throwable $t) {
            return $src;
        }
    }

    /**
     * @param resource|\GdImage $src
     * @return resource|\GdImage
     */
    public static function applyOrientationValueToGdImage($src, int $orientation)
    {
        if ($orientation <= 1) {
            return $src;
        }

        $rotated = $src;
        switch ($orientation) {
            case 2:
                if (function_exists('imageflip')) {
                    imageflip($rotated, IMG_FLIP_HORIZONTAL);
                }
                break;
            case 3:
                $tmp = imagerotate($rotated, 180, 0);
                if ($tmp !== false) {
                    imagedestroy($rotated);
                    $rotated = $tmp;
                }
                break;
            case 4:
                if (function_exists('imageflip')) {
                    imageflip($rotated, IMG_FLIP_VERTICAL);
                }
                break;
            case 5:
                if (function_exists('imageflip')) {
                    imageflip($rotated, IMG_FLIP_HORIZONTAL);
                }
                $tmp = imagerotate($rotated, 270, 0);
                if ($tmp !== false) {
                    imagedestroy($rotated);
                    $rotated = $tmp;
                }
                break;
            case 6:
                $tmp = imagerotate($rotated, 270, 0);
                if ($tmp !== false) {
                    imagedestroy($rotated);
                    $rotated = $tmp;
                }
                break;
            case 7:
                if (function_exists('imageflip')) {
                    imageflip($rotated, IMG_FLIP_HORIZONTAL);
                }
                $tmp = imagerotate($rotated, 90, 0);
                if ($tmp !== false) {
                    imagedestroy($rotated);
                    $rotated = $tmp;
                }
                break;
            case 8:
                $tmp = imagerotate($rotated, 90, 0);
                if ($tmp !== false) {
                    imagedestroy($rotated);
                    $rotated = $tmp;
                }
                break;
            default:
                break;
        }

        return $rotated;
    }

    /**
     * Rotate image 90 degrees clockwise.
     *
     * @param resource|\GdImage $src
     * @return resource|\GdImage|false
     */
    public static function rotateGdImage90Clockwise($src)
    {
        if (!function_exists('imagerotate')) {
            return false;
        }
        return imagerotate($src, -90, 0);
    }

    /**
     * Bake EXIF orientation into pixels and write JPEG to $outputPath.
     * Returns true if the image was transformed and written.
     */
    public static function bakeExifOrientationToJpegFile(string $inputPath, string $outputPath, int $quality = 88): bool
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return false;
        }

        $raw = @file_get_contents($inputPath);
        if ($raw === false) {
            return false;
        }

        $orientation = 1;
        if (function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($inputPath);
                $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
            } catch (\Throwable $t) {
                $orientation = 1;
            }
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return false;
        }

        $beforeW = imagesx($src);
        $beforeH = imagesy($src);

        $work = $src;
        if ($orientation > 1) {
            $work = self::applyOrientationValueToGdImage($src, $orientation);
            if ($work !== $src) {
                imagedestroy($src);
            }
        }

        $afterW = imagesx($work);
        $afterH = imagesy($work);
        $changed = $orientation > 1 || $beforeW !== $afterW || $beforeH !== $afterH;

        $ok = imagejpeg($work, $outputPath, $quality);
        imagedestroy($work);

        return $ok && $changed;
    }

    /**
     * Bake EXIF then rotate 90° CW (for manually fixing sideways files with no EXIF).
     */
    public static function bakeAndRotate90ClockwiseToJpegFile(string $inputPath, string $outputPath, int $quality = 88): bool
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return false;
        }

        $raw = @file_get_contents($inputPath);
        if ($raw === false) {
            return false;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return false;
        }

        $work = self::applyExifOrientationToGdImage($src, $inputPath);
        if ($work !== $src) {
            imagedestroy($src);
        }

        $rotated = self::rotateGdImage90Clockwise($work);
        if ($rotated === false) {
            imagedestroy($work);
            return false;
        }
        imagedestroy($work);

        $ok = imagejpeg($rotated, $outputPath, $quality);
        imagedestroy($rotated);

        return (bool) $ok;
    }
}
