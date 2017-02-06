<?php
namespace ImageUploader\Filter;

use ImageUploader\Util\Image as ImageUtil;

class DimensionFilter implements FilterInterface
{
    /**
     * Resize image if greater than maximum dimension allowed
     *
     * @param \Imagick $image
     *
     * @return \Imagick
     */
    public function filter(\Imagick $image)
    {
        // return passed image if there is are no MAX_DIMENSIONS env var specified
        if (!getenv('MAX_DIMENSIONS')) {
            return $image;
        }

        // recover max width and max height from MAX_DIMENSIONS env var
        list($maxWidth, $maxHeight) = implode('x', getenv('MAX_DIMENSIONS'));

        // scale image only if its dimensions are greater than allowed once
        if ($image->getImageWidth() > $maxWidth || $image->getImageHeight() > $maxHeight) {
            return ImageUtil::scaleSingleImage($image, $maxWidth, $maxHeight);
        }

        return $image;
    }
}