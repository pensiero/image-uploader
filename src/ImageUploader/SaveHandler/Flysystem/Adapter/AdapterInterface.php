<?php declare(strict_types=1);
namespace ImageUploader\SaveHandler\Flysystem\Adapter;

interface AdapterInterface
{
    /**
     * @param string $path
     * @param string $contents
     *
     * @return false|array
     */
    public function write($path, $contents);

    /**
     * @param string $path
     *
     * @return bool
     */
    public function has($path): bool;

    /**
     * @param string $path
     *
     * @return false|array
     */
    public function read($path);
}