<?php
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
     * @return array
     */
    public function getId();

    /**
     * Set the id of the image
     *
     * @param string $id
     */
    public function setId($id);

    /**
     * Return the public path of the image
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     */
    public function getPath($width = null, $height = null);

    /**
     * Return the local path of the image
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     */
    public function getLocalPath($width = null, $height = null);

    /**
     * Upload the imagick object (and eventually take in consideration that is a resized one if width and height are provided)
     * Return an info array
     *
     * @param \Imagick $image
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     */
    public function save(\Imagick $image, $width = null, $height = null);

    /**
     * Read the image
     * Return an info array
     *
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     */
    public function read($width = null, $height = null);
}