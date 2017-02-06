<?php declare(strict_types=1);
namespace ImageUploader\Validator;

use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\ValidationException;

class SizeValidator implements ValidatorInterface
{
    const MAX_SIZE = 10240; // Kb

    /**
     * Check if an image exceed the maximum allowed size
     *
     * @param \Imagick $image
     * @param int|null $width
     * @param int|null $height
     *
     * @return bool
     * @throws FlowException
     * @throws ValidationException
     */
    public function validate(\Imagick $image, $width = null, $height = null): bool
    {
        // return if there is no MAX_SIZE env var specified
        if (!getenv('MAX_SIZE')) {
            return true;
        }

        $maxSize = getenv('MAX_SIZE');

        // check if max size is an integer
        if (!is_numeric($maxSize)) {
            throw new FlowException('MAX_SIZE env var must be an integer');
        }

        // throw exception if image size exceed the maximum allowed
        if ($image->getImageLength() > (int) $maxSize * 1000) {
            throw new ValidationException('Maximum allowed filesize of ' . self::MAX_SIZE . 'KB exceeded');
        }

        return true;
    }
}