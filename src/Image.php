<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.main
 */

namespace CommunityCMS;

class Image extends File
{

    /**
     * Resize an image
     * @param string  $destination
     * @param integer $min_w       Minimum thumbnail width; cannot be 0
     * @param integer $min_h       Minimum thumbnail height; cannot be 0
     * @param integer $max_w       Maximum thumbnail width; 0 is no limit
     * @param integer $max_h       Maximum thumbnail height; 0 is no limit
     * @return \Image
     * @throws FileException
     */
    public function generateThumbnail(
        $destination,
        $min_w = 1,
        $min_h = 1,
        $max_w = 0,
        $max_h = 0
    ) {
        if (!$this->file) {
            throw new FileException('Cannot generate thumbnail.');
        }

        if ($destination != $this->file && file_exists(File::$file_root.$destination)) {
            throw new FileException('Desination file already exists.');
        }

        if ($min_w == 0 || $min_h == 0) {
            throw new FileException('Minimum dimensions must be greater than zero.');
        }

        if (preg_match('/\.png$/i', $this->file)) {
            $image     = imageCreateFromPNG(File::$file_root.$this->file);
            $imagetype = 'png';
        } elseif (preg_match('/\.(jpg|jpeg)$/i', $this->file)) {
            $image     = imageCreateFromJPEG(File::$file_root.$this->file);
            $imagetype = 'jpg';
        } else {
            throw new FileException('Cannot create thumbnail from file.');
        }

        $image_x = imagesx($image);
        $image_y = imagesy($image);

        // If maximum dimensions are set
        if ($max_h != 0 || $max_w != 0) {
            if ($max_h == 0 && $max_w != 0 && $image_x > $max_w) {
                $new_x = $max_w;
                $new_y = $image_y * ($new_x / $image_x);
            } elseif ($max_h != 0 && $max_w == 0 && $image_y > $max_h) {
                $new_y = $max_h;
                $new_x = $image_x * ($new_y / $image_y);
            } else {
                $new_x = $max_w;
                $new_y = $image_y * ($new_x / $image_x);
                if ($new_y > $max_w) {
                    $new_y = $max_h;
                    $new_x = $image_x * ($new_y / $image_y);
                }
            }
            // Prevent stretching
            if ($image_y < $new_y || $image_x < $new_x) {
                $new_y = $image_y;
                $new_x = $image_x;
            }
            // Handle minimum values
            if ($new_x < $min_w) {
                $new_x = $min_w;
            }
            if ($new_y < $min_h) {
                $new_y = $min_h;
            }
        } else {
            // No max value - one dimension has no upper limit
            if ($image_y >= $image_x) {
                $new_x = $min_w;
                $new_y = $image_y * ($new_x / $image_x);
            } else {
                $new_y = $min_h;
                $new_x = $image_x * ($new_y / $image_y);
            }
        }

        $thumb_image = imageCreateTrueColor($new_x, $new_y);

        imagealphablending($thumb_image, false);
        imagesavealpha($thumb_image, true);
        $transparent = imagecolorallocatealpha($thumb_image, 255, 255, 255, 127);
        imagefilledrectangle($thumb_image, 0, 0, $new_x, $new_y, $transparent);

        imagecopyresampled(
            $thumb_image,
            $image,
            0,
            0,
            0,
            0,
            $new_x,
            $new_y,
            $image_x,
            $image_y
        );
        if ($imagetype == 'png') {
            imagepng($thumb_image, File::$file_root.$destination);
        } else {
            imagejpeg($thumb_image, File::$file_root.$destination);
        }

        return new Image($destination);
    }
}
