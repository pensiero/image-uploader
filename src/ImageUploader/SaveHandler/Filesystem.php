<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler;

use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\NotFoundException;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Util\Request;

class Filesystem extends SaveHandler implements SaveHandlerInterface
{
    // filesystem directories
    const IMAGES_DIR = 'data/images';
    const THUMBS_DIR = 'data/thumbs';

    // public directory
    const PUBLIC_DIR = 'i';

    /**
     * Return path with format 1/2/3/4/1234.jpg (where 1234 it's the id of the image)
     *
     * @param string $id
     * @param string $dir
     *
     * @return string
     * @throws FlowException
     */
    private function generateDirsPath($id, $dir): string
    {
        // find the integer part inside the id
        preg_match('/(.*)-(\d+)(.*)/', $id, $matches);
        if (!isset($matches[2])) {
            throw new FlowException('Integer part of the image ID not found');
        }

        $integerPart = $matches[2];

        // split the integer part into an array of numbers
        $parts = array_map('intval', str_split($integerPart));

        // create directory if not present
        $path = implode('/', array_reverse($parts));

        if (!file_exists($dir . '/' . $path)) {
            mkdir($dir . '/' . $path . '/', 0777, true);
        }

        return $path;
    }

    /**
     * Complete path of the image
     *
     * @param array  $params
     * @param bool   $public
     *
     * @return string
     */
    private function getCompletePath($params = [], $public = false): string
    {
        $parts = [];

        // first directory
        $parts[] = $public
            ? self::PUBLIC_DIR
            : ($this->imageIsOriginal($params) ? self::IMAGES_DIR : self::THUMBS_DIR);

        // directories generated by integer part of the id
        if (!$public) {
            $filesystemDirectory = $this->imageIsOriginal($params) ? self::IMAGES_DIR : self::THUMBS_DIR;
            $parts[] = $this->generateDirsPath($this->id, $filesystemDirectory);
        }

        // filename
        $parts[] = $this->generateFilename($this->id, $params);

        return implode('/', $parts);
    }

    /**
     * Public path of the image
     *
     * @param null $width
     * @param null $height
     *
     * @return string
     * @throws FlowException
     */
    public function getUrl($width = null, $height = null): string
    {
        if (!$this->id) {
            throw new FlowException('ID must be initialized in order to get the image path');
        }

        return
            Request::serverUrl()
                . '/'
                . $this->getCompletePath([
                    'width'  => $width,
                    'height' => $height,
                ], true);
    }

    /**
     * Write the image on the filesystem
     *
     * @param \Imagick $image
     * @param null|int $width
     * @param null|int $height
     *
     * @return bool
     * @throws NotProvidedException
     */
    public function save(\Imagick $image, $width = null, $height = null): bool
    {
        // if width or height are provided, ID is required and cannot be null
        if (($width !== null || $height !== null) && $this->id == null) {
            throw new NotProvidedException('ID must be provided in order to resize an existent image');
        }

        // if there is no id, generate it
        if ($this->id === null) {
            $this->generateId();
        }

        // get the path where to save the image
        $path = $this->getCompletePath([
            'width'  => $width,
            'height' => $height,
        ]);

        // save blob
        $this->blob = $image->getImageBlob();

        // write the image on the filesystem
        $result = $image->writeImage($path);

        $image->destroy();

        return (bool) $result;
    }

    /**
     * Read image from the current defined ID
     *
     * @param null $width
     * @param null $height
     *
     * @return bool
     * @throws FlowException
     * @throws NotFoundException
     */
    public function read($width = null, $height = null): bool
    {
        if (!$this->id) {
            throw new FlowException('ID must be initialized in order to read image info');
        }

        $path = $this->getCompletePath([
            'width'  => $width,
            'height' => $height,
        ]);

        if (!file_exists($path)) {
            throw new NotFoundException();
        }

        $this->blob = file_get_contents($path);

        return true;
    }
}