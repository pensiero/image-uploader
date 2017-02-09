<?php declare(strict_types=1);
namespace ImageUploader\Entity;

use ImageUploader\Exception\FlowException;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Exception\NotFoundException;
use ImageUploader\Exception\ValidationException;
use ImageUploader\Filter\FilterInterface;
use ImageUploader\SaveHandler\SaveHandlerInterface;
use ImageUploader\Util\RemoteFile;
use ImageUploader\Util\Image as ImageUtil;
use ImageUploader\Validator\ValidatorInterface;

class Image
{
    const QUALITY = 90;

    /**
     * @var SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * @var ValidatorInterface[]
     */
    protected $validators = [];

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

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
        // check if the passed source is an url
        if (filter_var($source, FILTER_VALIDATE_URL)) {

            // check if the file exists
            if (!RemoteFile::checkIfExists($source)) {
                throw new NotFoundException("Image not found");
            }

            // create image from source
            $this->image = new \Imagick($source);
        }
        else {

            // create an Imagick from a base64 string
            $this->image = new \Imagick();
            $this->image->readImageBlob($source);
        }

        // check validators
        foreach ($this->validators as $validator) {
            $validator->validate($this->image);
        }

        // apply filters
        foreach ($this->filters as $filter) {
            $this->image = $filter->filter($this->image);
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
    private function resize($width, $height): \Imagick
    {
        if ($this->image === null) {
            throw new NotProvidedException('Image must be created in order to resize it');
        }

        // check validators
        foreach ($this->validators as $validator) {
            $validator->validate($this->image, $width, $height);
        }

        // resize image
        $image = ImageUtil::scaleSingleImage($this->image, (int) $width, (int) $height);

        // apply filters
        foreach ($this->filters as $filter) {
            $image = $filter->filter($image);
        }

        return $image;
    }

    /**
     * Get info about the current image
     *
     * @param null $width
     * @param null $height
     *
     * @return array
     */
    private function info($width = null, $height = null): array
    {
        return [
            'status_code'  => 200,
            'id'           => $this->saveHandler->getId(),
            'url'          => $this->saveHandler->getUrl($width, $height),
            'url_dynamic'  => $this->saveHandler->getUrl('#WIDTH#', '#HEIGHT#'),
            'width'        => $width,
            'height'       => $height,
        ];
    }

    /**
     * Get the blob of the image
     *
     * @return string
     */
    public function getBlob(): string
    {
        return $this->saveHandler->getBlob();
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
    public function upload($source, $width = null, $height = null): array
    {
        // no save handler provided
        if ($this->saveHandler === null) {
            throw new NotProvidedException('SaveHandler must be provided in order to upload the image somewhere');
        }

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
            catch (ValidationException $e) {
                return [
                    'status_code' => 500,
                    'message'     => $e->getMessage(),
                ];
            }
        }

        // null width and height if empty
        $width = !empty($width) ? $width : null;
        $height = !empty($height) ? $height : null;


        try {

            // use the original image or resize it before uploading
            $image = $width === null && $height === null
                ? $this->image
                : $this->resize($width, $height);
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
        catch (ValidationException $e) {
            return [
                'status_code' => 500,
                'message'     => $e->getMessage(),
            ];
        }

        // save the image (params should not to be passed, we are dealing with the original image that will be used for future resizes)
        if (!$this->saveHandler->save($image)) {
            return [
                'status_code' => 500,
                'message'     => 'Error while uploading the image',
            ];
        }

        return $this->info();
    }

    /**
     * Return the path of an image, passing its id
     * If specified width and height, check if there is or eventually create it
     *
     * @param string   $id
     * @param int|null $width
     * @param int|null $height
     *
     * @return array
     * @throws NotFoundException
     * @throws NotProvidedException
     */
    public function read($id, $width = null, $height = null): array
    {
        if ($this->saveHandler === null) {
            throw new NotProvidedException('SaveHandler must be provided in order to upload the image somewhere');
        }

        // null width and height if empty
        $width = !empty($width) ? $width : null;
        $height = !empty($height) ? $height : null;

        // force a particular id
        $this->saveHandler->setId($id);

        // search the image with the specified params
        try {
            $this->saveHandler->read($width, $height);
        }
        catch (NotFoundException $e) {

            // if not found and there are no params, return 404
            if ($width === null && $height === null) {
                return [
                    'status_code' => 404,
                    'message'     => 'Image not found',
                ];
            }

            // init the saveHandler with the original image (without params)
            $this->saveHandler->read();

            try {

                // create the Imagick entity from the image path
                $this->create($this->getBlob());

                // resize the image
                $image = $this->resize($width, $height);
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
            catch (ValidationException $e) {
                return [
                    'status_code' => 500,
                    'message'     => $e->getMessage(),
                ];
            }

            // save the image (params should not to be passed, we are dealing with the original image that will be used for future resizes)
            if (!$this->saveHandler->save($image, $width, $height)) {
                return [
                    'status_code' => 500,
                    'message'     => 'Error while uploading the resizing image',
                ];
            }
        }
        catch (FlowException $e) {
            return [
                'status_code' => 500,
                'message'     => 'Hey men! I accept only images saved by me in the past! Gimme the correct url :/',
            ];

        }

        return $this->info($width, $height);
    }

    /**
     * @return \Imagick
     */
    public function getImage(): \Imagick
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

    /**
     * @param FilterInterface[] ...$filters
     */
    public function setFilters(FilterInterface ...$filters)
    {
        foreach ($filters as $filter) {
            $this->filters[] = $filter;
        }
    }

    /**
     * @param ValidatorInterface[] ...$validators
     */
    public function setValidators(ValidatorInterface ...$validators)
    {
        foreach ($validators as $validator) {
            $this->validators[] = $validator;
        }
    }
}