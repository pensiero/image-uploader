<?php
namespace ImageUploader\Validator;

use ImageUploader\Exception\ValidationException;

class SizeValidator implements ValidatorInterface
{
    const MAX_SIZE = 10240; // Kb

    /**
     * @param \Imagick $image
     * @param int|null $width
     * @param int|null $height
     *
     * @throws ValidationException
     *
     * @return bool
     */
    public function validate(\Imagick $image, $width = null, $height = null)
    {
        if ($image->getImageLength() > self::MAX_SIZE * 1000) {
            throw new ValidationException('Maximum allowed filesize of ' . self::MAX_SIZE . 'KB exceeded');
        }

        return true;
    }
}