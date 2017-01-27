<?php
namespace ImageUploader\SaveHandler;
use Aws\S3\S3Client;
use ImageUploader\Exception\NotFoundException;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class Aws implements SaveHandlerInterface
{
    public function info($id, $width = null, $height = null)
    {
        // TODO: Implement info() method.
    }

    public function save(\Imagick $image, $width = null, $height = null)
    {
        // TODO: Implement save() method.
    }

    public function read($id, $width = null, $height = null)
    {
        // TODO: This should stay in a costruct ?
        $client = S3Client::factory([
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ],
            'regionzc' => getenv('AWS_REGION'),
            'version' => 'latest|version',
        ]);

        $adapter = new AwsS3Adapter($client, getenv('AWS_BUCKET'));

        $originalPath = '/original/' . $id;
        $remotePath = $originalPath;
        if ($width != null) {
            $remotePath = '/' . $width . 'x' . $height . '/' . $id;
        }


        // TODO: This should stay in a GenericAdapter (only for images?)
        if ($adapter->has($remotePath)) {
            return $adapter->get($remotePath);
        }

        if (!$adapter->has($originalPath)) {
            throw new NotFoundException('This Image cannot be generated because original image doesn\'t exist');
        }

        $originalFile = $adapter->get($originalPath);

        Image::configure(array('driver' => 'imagick'));

        $editedImage = Image::make($originalFile);
        $editedImage->fit($width, $height, function ($constraint) {
            $constraint->upsize();
        },'center');
        $editedImage->encode('jpg', 85);

        $resizedRemotePath = '/' . $width . 'x' . $height . '/' . $id;
        $adapter->put($resizedRemotePath, $editedImage->__toString(), 'public');

        return $editedImage;
    }
}