<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler;

class Aws implements SaveHandlerInterface
{
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    public function setId($id)
    {
        // TODO: Implement setId() method.
    }

    public function getPath($width = null, $height = null): string
    {
        // TODO: Implement getPath() method.
    }

    public function getLocalPath($width = null, $height = null): string
    {
        // TODO: Implement getLocalPath() method.
    }

    public function save(\Imagick $image, $width = null, $height = null): bool
    {
        // TODO: Implement save() method.
    }

    public function read($width = null, $height = null): bool
    {
        // TODO: Implement read() method.
    }
}