<?php
namespace ImageUploader\Validator;

use ImageUploader\Exception\ValidationException;

interface ValidatorInterface
{
    /**
     * @param \Imagick $image
     * @param int|null $width
     * @param int|null $height
     *
     * @throws ValidationException
     *
     * @return bool
     */
    public function validate(\Imagick $image, $width = null, $height = null);
}