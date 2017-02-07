<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler;

use Aws\S3\S3Client;
use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\NotFoundException;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Util\Request;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;

class Aws extends SaveHandler implements SaveHandlerInterface
{
    // filesystem directories
    const IMAGES_DIR = 'images';
    const THUMBS_DIR = 'thumbs';

    // public directory
    const PUBLIC_DIR = 'i';

    /**
     * @var S3Client
     */
    protected $client;

    /**
     * @var AwsS3Adapter
     */
    protected $adapter;

    public function __construct()
    {
        $this->client = new S3Client([
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ],
            'region'      => getenv('AWS_REGION'),
            'version'     => 'latest',
        ]);

        $this->adapter = new AwsS3Adapter($this->client, getenv('AWS_BUCKET'));
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

        // filename
        $parts[] = $this->generateFilename($this->id, $params);

        return implode('/', $parts);
    }

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

        $path = $this->getCompletePath([
            'width'  => $width,
            'height' => $height,
        ]);

        $config = new Config([
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            'mimetype'   => 'application/json',
        ]);

        $result = $this->adapter->write($path, $image->getImageBlob(), $config);

        $image->destroy();

        return (bool) $result;
    }

    /**
     * Read image from the current defined ID
     *
     * @param null|int $width
     * @param null|int $height
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

        if (!$this->adapter->has($path)) {
            throw new NotFoundException();
        }

        $response = $this->adapter->read($path);
        if (!$response) {
            throw new NotFoundException();
        }

        $this->blob = $response['contents'];

        return true;
    }
}