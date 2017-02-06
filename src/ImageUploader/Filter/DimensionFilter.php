<?php
namespace ImageUploader\Filter;

use ImageUploader\Exception\FlowException;
use ImageUploader\Util\Image as ImageUtil;

class DimensionFilter implements FilterInterface
{
    /**
     * Resize image if greater than maximum dimension allowed
     *
     * @param \Imagick $image
     *
     * @return \Imagick
     * @throws FlowException
     */
    public function filter(\Imagick $image)
    {
        // return passed image if there is are no MAX_DIMENSIONS env var specified
        if (!getenv('MAX_DIMENSIONS')) {
            return $image;
        }

        // recover max width and max height from MAX_DIMENSIONS env var
        list($maxWidth, $maxHeight) = implode('x', getenv('MAX_DIMENSIONS'));

        // check if max size is an integer
        if (!is_numeric($maxWidth) || !is_numeric($maxHeight) ) {
            throw new FlowException('Each dimension specified in the MAX_DIMENSIONS env var must be an integer');
        }

        // scale image only if its dimensions are greater than allowed once
        if ($image->getImageWidth() > $maxWidth || $image->getImageHeight() > $maxHeight) {
            return ImageUtil::scaleSingleImage($image, $maxWidth, $maxHeight);
        }

        return $image;
    }
}