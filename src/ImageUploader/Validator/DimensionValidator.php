<?php declare(strict_types=1);
namespace ImageUploader\Validator;

use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\ValidationException;

class DimensionValidator implements ValidatorInterface
{
    /**
     * Check dimensions (width and height) of an image and compare them with the allowed dimensions
     *
     * @param \Imagick $image
     * @param int|null $width
     * @param int|null $height
     *
     * @throws ValidationException
     *
     * @return bool
     */
    public function validate(\Imagick $image, $width = null, $height = null): bool
    {
        // all dimensions are allowed
        if (!getenv('ALLOWED_DIMENSIONS')) {
            return true;
        }

        // dimensions are compared only against future params
        if ($width === null && $height === null) {
            return true;
        }

        // explode env vars into an array of allowed dimensions
        $dimensions = explode(';', getenv('ALLOWED_DIMENSIONS'));

        // filter all dimensions searching for a specific one
        $dimensions = array_filter($dimensions, function($dimension) use ($width, $height) {

            // recover width and height from dimension env var
            list($dimensionWidth, $dimensionHeight) = explode('x', $dimension);

            // check if max size is an integer
            if (!is_numeric($dimensionWidth) || !is_numeric($dimensionHeight)) {
                throw new FlowException('Each dimension specified in the ALLOWED_DIMENSIONS env var must be an integer');
            }

            return (int) $dimensionWidth === (int) $width && (int) $dimensionHeight === (int) $height;
        });

        // there are no valid dimensions
        if (empty($dimensions)) {
            throw new ValidationException('The requested dimension (' . ($width ?? 0) . 'x' . ($height ?? 0) . ') is not an allowed dimension');
        }

        return true;
    }
}