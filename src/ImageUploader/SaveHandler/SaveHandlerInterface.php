<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler;

/**
 * Interface SaveHandlerInterface
 *
 * @package ImageUploader\SaveHandler
 */
interface SaveHandlerInterface
{
    /**
     * Return the uniq identifier of the image
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set the id of the image
     *
     * @param string $id
     */
    public function setId(string $id);

    /**
     * Return the public path of the image
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return string
     */
    public function getPath($width = null, $height = null): string;

    /**
     * Return the local path of the image
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return string
     */
    public function getLocalPath($width = null, $height = null): string;

    /**
     * Upload the imagick object (and eventually take in consideration that is a resized one if width and height are provided)
     *
     * @param \Imagick $image
     * @param int|null $width
     * @param int|null $height
     *
     * @return bool
     */
    public function save(\Imagick $image, $width = null, $height = null): bool;

    /**
     * Read image from the current defined ID
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return bool
     */
    public function read($width = null, $height = null): bool;
}