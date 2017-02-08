<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler\Flysystem\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface as FlysystemAdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config;

class AwsAdapter implements AdapterInterface
{
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

    public function write($path, $contents)
    {
        $config = new Config([
            'visibility' => FlysystemAdapterInterface::VISIBILITY_PUBLIC,
            'mimetype'   => 'application/json',
        ]);

        return $this->adapter->write($path, $contents, $config);
    }

    public function has($path): bool
    {
        return $this->adapter->has($path);
    }

    public function read($path)
    {
        return $this->adapter->read($path);
    }
}