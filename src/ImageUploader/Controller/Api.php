<?php
namespace ImageUploader\Controller;

use ImageUploader\Entity\Image;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\SaveHandler\Filesystem;
use ImageUploader\Validator\SizeValidator;
use ImageUploader\Validator\DimensionValidator;

class Api
{
    /**
     * @var Image
     */
    private $image;

    /**
     * Initializator constructor.
     */
    public function __construct()
    {
        // init the Image entity
        $this->image = new Image();

        // set the save handler
        $this->image->setSaveHandler(new Filesystem());

        // set the validators
        $this->image->setValidators([
            new SizeValidator(),
            new DimensionValidator(),
        ]);
    }

    /**
     * Read an image
     *
     * @return array
     */
    private function read()
    {
        $id = filter_input(INPUT_GET, 'id');

        // if id is not provided, play ping pong
        if (!$id) {
            return [
                'ping' => 'pong',
            ];
        }

        // width and height
        $width = filter_input(INPUT_GET, 'width', FILTER_SANITIZE_NUMBER_INT) ?: null;
        $height = filter_input(INPUT_GET, 'height', FILTER_SANITIZE_NUMBER_INT) ?: null;

        return $this->image->read($id, $width, $height);
    }

    /**
     * Create an image
     */
    private function create()
    {
        if ($_SERVER['CONTENT_TYPE'] == 'application/json') {
            $data = json_decode(file_get_contents('php://input'), true);
            $source = $data['source'];
        }
        else {
            $source = !empty($_FILES)
                ? $_FILES['source']['tmp_name']
                : filter_input(INPUT_POST, 'source');
        }

        // source is required
        if (!$source) {
            throw new NotProvidedException('SOURCE param must be provided in a base64 encoded format (without "new lines") in order to get an image path');
        }

        // width and height
        $width = filter_input(INPUT_POST, 'width', FILTER_SANITIZE_NUMBER_INT) ?: null;
        $height = filter_input(INPUT_POST, 'height', FILTER_SANITIZE_NUMBER_INT) ?: null;

        return $this->image->upload($source, $width, $height);
    }

    /**
     * Echo the response in JSON format
     *
     * @param $response
     */
    private function echoResponse($response)
    {
        header('Content-Type: application/json');

        echo json_encode($response);

        exit();
    }

    public function init()
    {
        // read request
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->echoResponse($this->read());
        }

        // create request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->echoResponse($this->create());
        }
    }
}