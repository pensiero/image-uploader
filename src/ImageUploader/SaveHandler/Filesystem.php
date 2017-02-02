<?php
namespace ImageUploader\SaveHandler;

use ImageUploader\Exception\NotFoundException;
use ImageUploader\Util\Crypt;
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
     * Filesystem constructor
     */
    public function __construct()
    {
        // generate a random string as unique id
        $this->id = str_replace('.', '_', uniqid('', true));
    }

    /**
     * Return path with format 1/2/3/4/1234.jpg (where 1234 it's the id of the image)
     *
     * @param string $id
     * @param string $dir
     *
     * @return string
     */
    private function generateDirsPath($id, $dir)
    {
        // use as id only the integer part (what's after '_') of the uniq id
        $integerId = ltrim(substr($id, strpos($id, '_')), '_');

        $parts = array_map('intval', str_split($integerId));

        // create directory if not present
        $path = implode('/', array_reverse($parts));

        if (!file_exists($dir . '/' . $path)) {
            mkdir($dir . '/' . $path . '/', 0777, true);
        }

        return $path;
    }

    /**
     * Generate the file name of the image basing on id and params
     *
     * @param string $id
     * @param array  $params
     *
     * @return string
     */
    private function generateFilename($id, $params = [])
    {
        // merge id with params
        $data = array_merge([$id], $params);

        // the imploded params are useful for the cache of the elaborated image
        return (new Crypt())->encryptArrayIntoString($data) . '.' . self::FORMAT_DEFAULT;
    }

    /**
     * Get id from filname (decrypt it)
     *
     * @param $filename
     *
     * @return string
     * @throws NotFoundException
     */
    private function generateIdFromFilename($filename)
    {
        $data = (new Crypt())->decryptArrayFromString($filename);

        if (!isset($data[0])) {
            throw new NotFoundException();
        }

        return $data[0];
    }

    /**
     * Complete path of the image
     *
     * @param string $id
     * @param array  $params
     * @param bool   $showPublicDirectories
     *
     * @return string
     */
    private function getCompletePath($id, $params = [], $showPublicDirectories = false)
    {
        $dir = !$showPublicDirectories
            ? (empty($params) ? self::IMAGES_DIR : self::THUMBS_DIR)
            : (empty($params) ? self::IMAGES_DIR_PUBLIC : self::THUMBS_DIR_PUBLIC);

        $filesystemDirectory = empty($params) ? self::IMAGES_DIR : self::THUMBS_DIR;

        return $dir . '/' . $this->generateDirsPath($id, $filesystemDirectory) . '/' . $this->generateFilename($id, $params);
    }

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
     * Path of the image
     *
     * @param null $width
     * @param null $height
     *
     * @return string
     */
    public function getPath($width = null, $height = null)
    {
        return
            Request::serverUrl()
            . '/'
            . $this->getCompletePath($this->id, [
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
     */
    public function save(\Imagick $image, $width = null, $height = null)
    {
        $result = $image->writeImage(
            $this->getCompletePath($this->id, [
                'width'  => $width,
                'height' => $height,
            ])
        );

        $image->destroy();

        return $result;
    }

    /**
     * Read the image from the filesystem
     *
     * @param string   $filename
     * @param null|int $width
     * @param null|int $height
     *
     * @return bool
     * @throws NotFoundException
     */
    public function read($filename, $width = null, $height = null)
    {
        $id = $this->generateIdFromFilename($filename);

        if (!file_exists($this->getCompletePath($id, [
            'width'  => $width,
            'height' => $height,
        ]))) {
            throw new NotFoundException();
        }

        // very important, set the id of the save handler as the current one generated from decryption
        $this->id = $id;

        return true;
    }
}