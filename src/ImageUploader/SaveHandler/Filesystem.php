<?php
namespace ImageUploader\SaveHandler;

use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\NotFoundException;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Util\Request;

class Filesystem implements SaveHandlerInterface
{
    // filesystem directories
    const IMAGES_DIR = 'data/images';
    const THUMBS_DIR = 'data/thumbs';

    // public directories
    const IMAGES_DIR_PUBLIC = 'i';
    const THUMBS_DIR_PUBLIC = 't';

    // default image format
    const FORMAT_DEFAULT = 'jpg';

    /**
     * @var string
     */
    protected $id;

    /**
     * ID of the image
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
    public function getPath($width = null, $height = null)
    {
        if (!$this->id) {
            throw new FlowException('ID must be initialized in order to get the image path');
        }

        return
            Request::serverUrl()
            . '/'
            . $this->getCompletePath($this->id, [
                'width'  => $width,
                'height' => $height,
            ], true);
    }

    /**
     * Local path of the image
     *
     * @param null $width
     * @param null $height
     *
     * @return string
     * @throws FlowException
     */
    public function getLocalPath($width = null, $height = null)
    {
        if (!$this->id) {
            throw new FlowException('ID must be initialized in order to get the image path');
        }

        return
            $this->getCompletePath($this->id, [
                'width'  => $width,
                'height' => $height,
            ]);
    }

    /**
     * Generate a random string as unique id
     *
     * @return string
     */
    private function generateId()
    {
        $this->id = str_replace('.', '-', uniqid('', true));

        return $this->id;
    }

    /**
     * Attach the passed params to the id in order to generate the filename
     *
     * @param array $params
     *
     * @return string
     */
    private function generateFilename($id, $params = [])
    {
        $paramsString = '';

        if (!empty($params)) {

            // replace null values with zero
            $params = array_map(function($param) {
                return empty($param) ? 0 : $param;
            }, $params);

            $paramsString = '_' . implode($params, '_');
        }

        // concatenate id, params and extension
        return $id . $paramsString . '.' . self::FORMAT_DEFAULT;
    }

    /**
     * Return path with format 1/2/3/4/1234.jpg (where 1234 it's the id of the image)
     *
     * @param string $id
     * @param string $dir
     *
     * @return string
     * @throws FlowException
     */
    private function generateDirsPath($id, $dir)
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
     * Image is considered "original" if there are only empty params
     *
     * @param $params
     *
     * @return bool
     */
    private function imageIsOriginal($params)
    {
        return empty(array_filter($params, function($param) {
            return !empty($param);
        }));
    }

    /**
     * Analyze the image path and recover the original ID of the image
     *
     * @param $path
     *
     * @return string
     * @throws FlowException
     */
    private function generateIdFromPath($path)
    {
        preg_match('/\/data\/(images|thumbs)\/(.*)\/(.*-\d+)_(.*)\.(.*)/', $path, $matches);

        if (!isset($matches[3])) {
            throw new FlowException('Image ID not found inside the string');
        }

        return $matches[3];
    }

    /**
     * Complete path of the image
     *
     * @param string $id
     * @param array  $params
     * @param bool   $public
     *
     * @return string
     */
    private function getCompletePath($id = null, $params = [], $public = false)
    {
        if ($id === null) {
            $id = $this->generateId();
        }

        $parts = [];

        // directory
        $parts[] = $public
            ? ($this->imageIsOriginal($params) ? self::IMAGES_DIR_PUBLIC : self::THUMBS_DIR_PUBLIC)
            : ($this->imageIsOriginal($params) ? self::IMAGES_DIR : self::THUMBS_DIR);

        if (!$public) {
            $filesystemDirectory = $this->imageIsOriginal($params) ? self::IMAGES_DIR : self::THUMBS_DIR;
            $parts[] = $this->generateDirsPath($id, $filesystemDirectory);
        }

        $parts[] = $this->generateFilename($id, $params);

        return implode('/', $parts);
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
    public function save(\Imagick $image, $width = null, $height = null)
    {
        // if width or height are provided, ID is required and cannot be null
        if (($width !== null || $height !== null) && $this->id == null) {
            throw new NotProvidedException('ID must be provided in order to resize an existent image');
        }

        $path = $this->getCompletePath($this->id, [
            'width'  => $width,
            'height' => $height,
        ]);

        $result = $image->writeImage($path);

        $image->destroy();

        return $result;
    }

    /**
     * Read image from the current defined ID
     *
     * @param string $id
     * @param null   $width
     * @param null   $height
     *
     * @return bool
     * @throws NotFoundException
     */
    public function read($id, $width = null, $height = null)
    {
        // generate id based on filepath and set the id as the current one
        $this->id = $id;

        if (!file_exists($this->getCompletePath($this->id, [
            'width'  => $width,
            'height' => $height,
        ]))) {
            throw new NotFoundException();
        }

        return true;
    }
}