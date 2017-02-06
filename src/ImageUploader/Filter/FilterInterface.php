<?php declare(strict_types=1);
namespace ImageUploader\Filter;

interface FilterInterface
{
    /**
     * @param \Imagick $image
     *
     * @return \Imagick
     */
    public function filter(\Imagick $image): \Imagick;
}