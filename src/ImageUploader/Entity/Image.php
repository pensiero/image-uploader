<?php
namespace ImageUploader\Entity;

use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Exception\NotFoundException;
use ImageUploader\SaveHandler\SaveHandlerInterface;
use ImageUploader\Util\RemoteFile;
use ImageUploader\Util\Image as ImageUtil;

class Image
{
    const QUALITY = 90;

    const MAX_SIZE = 10240; // Kb

    const MAX_WIDTH = 4096; // Kb

    const MAX_HEIGHT = 4096; // Kb

    const OPTIMIZE = true;

    /**
     * @var SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * @var \Imagick
     */
    protected $image;

    /**
     * Create the Imagick object
     *
     * @param string $source
     *
     * @throws FlowException
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

        if ($this->image->getImageLength() > self::MAX_SIZE * 1000) {
            throw new FlowException('Maximum allowed filesize of ' . self::MAX_SIZE . 'KB exceeded');
        }

        // remove exif informations (optimization)
        if (self::OPTIMIZE) {
            $this->image->stripImage();
        }

        // resize image if greater than maximum dimension allowed
        if ($this->image->getImageWidth() > self::MAX_WIDTH || $this->image->getImageHeight() > self::MAX_HEIGHT) {
            $this->image->scaleImage(self::MAX_WIDTH, self::MAX_HEIGHT, true);
        }
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

        return ImageUtil::scaleSingleImage($this->image, $width, $height);
    }

    private function info($width, $height)
    {
        return [
            'status_code' => 200,
            'id'          => $this->saveHandler->getId(),
            'path'        => $this->saveHandler->getPath(),
            'width'       => $width,
            'height'      => $height,
            'optimized'   => self::OPTIMIZE,
        ];
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
            try {
                $this->create($source);
            }
            catch (FlowException $e) {
                return [
                    'status_code' => 422,
                    'message'     => $e->getMessage(),
                ];
            }
            catch (NotFoundException $e) {
                return [
                    'status_code' => 404,
                    'message'     => $e->getMessage(),
                ];
            }
        }

        // null width and height if empty
        $width = !empty($width) ? $width : null;
        $height = !empty($height) ? $height : null;

        // use the original image or resize it
        $image = $width === null && $height === null
            ? $this->image
            : $this->resize($width, $height);

        // no save handler provided
        if ($this->saveHandler === null) {
            throw new NotProvidedException('SaveHandler must be provided in order to upload the image somewhere');
        }

        // save the image
        if ($this->saveHandler->save($image, $width, $height)) {
            return $this->info($width, $height);
        }
    }

    /**
     * Return the path of an image, passing its filename
     * If specified width and height, check if there is or eventually create it
     *
     * @param string   $filename
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     * @throws NotFoundException
     * @throws NotProvidedException
     */
    public function read($filename, $width = null, $height = null)
    {
        if ($this->saveHandler === null) {
            throw new NotProvidedException('SaveHandler must be provided in order to upload the image somewhere');
        }

        // null width and height if empty
        $width = !empty($width) ? $width : null;
        $height = !empty($height) ? $height : null;

        // search the image with the specified params
        try {
            // when we read from the savehandler, the id of the image is setted
            $this->saveHandler->read($filename, $width, $height);
        }
        catch (NotFoundException $e) {

            // if not found and there are no params, return 404
            if ($width === null && $height === null) {
                return [
                    'status_code' => 404,
                    'message'     => 'Image not found',
                ];
            }

            // find the image with the uniq identifier
            $this->read($filename);

            // upload the new thumbnail
            return $this->upload($this->saveHandler->getPath($width, $height), $width, $height);
        }

        return $this->info($width, $height);
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