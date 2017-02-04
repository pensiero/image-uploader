<?php
namespace ImageUploader\Controller;

use ImageUploader\Entity\Image;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\SaveHandler\Filesystem;

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
        $this->image = new Image();
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
        $source = filter_input(INPUT_POST, 'source');
        if (!empty($_FILES)) {
            $source = $_FILES['source']['tmp_name'];
        }

        // id is required
        if (!$source) {
            throw new NotProvidedException('SOURCE must be provided in order to get an image path');
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
        // init the Image entity
        $this->image->setSaveHandler(new Filesystem());

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