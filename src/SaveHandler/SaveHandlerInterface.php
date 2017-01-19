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
     * Return an array of useful informations (id, filepath, width, height etc)
     *
     * @param string   $id
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     */
    public function info($id, $width = null, $height = null);

    /**
     * Upload the imagick object (and eventually take in consideration that is a resized one if width and height are provided)
     * Return an info array
     *
     * @param \Imagick $image
     * @param int|null     $width
     * @param int|null     $height
     *
     * @return array
     */
    public function save(\Imagick $image, $width = null, $height = null);

    /**
     * Read the image
     * Return an info array
     *
     * @param string   $id
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     */
    public function read($id, $width = null, $height = null);
}