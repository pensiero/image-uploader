<?php
namespace ImageUploader\SaveHandler;

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
    private function getDirsPath($id, $dir)
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
    private function getFilename($id, $params = [])
    {
        // the imploded params are useful for the cache of the elaborated image
        return md5($id . 'oscar' . implode('_', $params)) . '.' . self::FORMAT_DEFAULT;
    }

    /**
     * Complete path of the image
     *
     * @param string $id
     * @param array  $params
     *
     * @return string
     */
    private function getCompletePath($id, $params = [], $showPublicDirectories = false)
    {
        $dir = !$showPublicDirectories
            ? (empty($params) ? self::IMAGES_DIR : self::THUMBS_DIR)
            : (empty($params) ? self::IMAGES_DIR_PUBLIC : self::THUMBS_DIR_PUBLIC);

        $filesystemDirectory = empty($params) ? self::IMAGES_DIR : self::THUMBS_DIR;

        return $dir . '/' . $this->getDirsPath($id, $filesystemDirectory) . '/' . $this->getFilename($id, $params);
    }

    /**
     * Return info about a requested image
     *
     * @param string   $id
     * @param null|int $width
     * @param null|int $height
     *
     * @return array
     */
    public function info($id, $width = null, $height = null)
    {
        return [
            'status_code' => 200,
            'id'          => $id,
            'path'        => Request::serverUrl() . '/' . $this->getCompletePath($id, [
                'width'  => $width,
                'height' => $height,
            ], true),
            'width'       => $width,
            'height'      => $height,
        ];
    }

    /**
     * Write the image on the filesystem
     *
     * @param \Imagick $image
     * @param null|int $width
     * @param null|int $height
     *
     * @return array
     */
    public function save(\Imagick $image, $width = null, $height = null)
    {
        $image->writeImage(
            $this->getCompletePath($this->id, [
                'width'  => $width,
                'height' => $height,
            ])
        );

        $image->destroy();

        return $this->info($this->id, $width, $height);
    }

    /**
     * Read the image from the filesystem
     *
     * @param string $id
     * @param null|int $width
     * @param null|int $height
     *
     * @return array
     */
    public function read($id, $width = null, $height = null)
    {
        if (!file_exists($this->getCompletePath($id, [
            'width'  => $width,
            'height' => $height,
        ]))) {
            return [
                'status_code' => 404,
                'message'     => 'File not found',
            ];
        }

        return $this->info($id, $width, $height);
    }
}