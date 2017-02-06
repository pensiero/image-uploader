<?php
namespace ImageUploader\Filter;

class OptimizeFilter implements FilterInterface
{
    /**
     * Optimize the image
     *
     * @param \Imagick $image
     *
     * @return \Imagick
     */
    public function filter(\Imagick $image)
    {
        // all dimensions are allowed
        if (!getenv('OPTIMIZE')) {
            return $image;
        }

        // remove exif informations (optimization)
        $image->stripImage();

        return $image;
    }
}