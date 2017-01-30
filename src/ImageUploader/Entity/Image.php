<?php
namespace ImageUploader\Entity;

use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Exception\NotFoundException;
use ImageUploader\SaveHandler\SaveHandlerInterface;
use ImageUploader\Util\RemoteFile;

class Image
{
    /**
     * @var SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * @var \Imagick
     */
    protected $image;

    /**
     * @param string $source
     *
     * @throws NotFoundException
     */
    private function create($source)
    {
        // check if the passed source is an url or not
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            if (!RemoteFile::checkIfExists($source)) {
                throw new NotFoundException("Image not found");
            }
        }

        $this->image = new \Imagick($source);
    }

    /**
     * Resize the current saved image
     *
     * @param int $width
     * @param int $height
     *
     * @return \Imagick
     * @throws NotProvidedException
     */
    private function resize($width, $height)
    {
        if ($this->image === null) {
            throw new NotProvidedException('Image must be created in order to resize it');
        }

        // TODO image resize
        $image = $this->image;

        return $image;
    }

    /**
     * Upload an image (eventually resizing it)
     * Return the filepath of the saved image
     *
     * @param string   $source      // absolute url or blob
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     * @throws NotProvidedException
     */
    public function upload($source, $width = null, $height = null)
    {
        // if image is not present, create it from source
        if ($this->image === null) {
            $this->create($source);
        }

        // use the original image or resize it
        $image = $width === null && $height === null
            ? $this->image
            : $this->resize($width, $height);

        // no save handler provided
        if ($this->saveHandler === null) {
            throw new NotProvidedException('SaveHandler must be provided in order to upload the image somewhere');
        }

        // save the image
        return $this->saveHandler->save($image, $width, $height);
    }

    /**
     * Return the path of an image, passing its id
     * If specified width and height, check if there is or eventually create it
     *
     * @param string $id
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     * @throws NotFoundException
     * @throws NotProvidedException
     */
    public function read($id, $width = null, $height = null)
    {
        if ($this->saveHandler === null) {
            throw new NotProvidedException('SaveHandler must be provided in order to upload the image somewhere');
        }

        // search the image with the specified params
        $imagePath = $this->saveHandler->read($id, $width, $height);
        if ($imagePath) {
            return $imagePath;
        }

        // if not found and there are no params (original requested), throw exception
        if ($width !== null && $height !== null) {
            throw new NotFoundException('No image found in the save handler');
        }

        // return the image path of the original image
        $imagePath = $this->read($id);

        return $this->upload($imagePath, $width, $height);
    }

    /**
     * @return \Imagick
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param SaveHandlerInterface $saveHandler
     */
    public function setSaveHandler($saveHandler)
    {
        $this->saveHandler = $saveHandler;
    }
}