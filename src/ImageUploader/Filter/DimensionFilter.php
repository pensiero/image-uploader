<?php
namespace ImageUploader\Filter;

use ImageUploader\Util\Image as ImageUtil;

class DimensionFilter implements FilterInterface
{
    const MAX_WIDTH = 4096; // Kb

    const MAX_HEIGHT = 4096; // Kb

    /**
     * Resize image if greater than maximum dimension allowed
     *
     * @param \Imagick $image
     *
     * @return \Imagick
     */
    public function filter(\Imagick $image)
    {
        if ($image->getImageWidth() > self::MAX_WIDTH || $image->getImageHeight() > self::MAX_HEIGHT) {
            return ImageUtil::scaleSingleImage($image, self::MAX_WIDTH, self::MAX_HEIGHT);
        }

        return $image;
    }
}