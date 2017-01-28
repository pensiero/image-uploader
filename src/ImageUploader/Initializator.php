<?php
namespace ImageUploader;

use ImageUploader\Entity\Image;
use ImageUploader\Exception\NotProvidedException;

class Initializator
{
    /**
     * @var Image
     */
    private $image;

    /**
     * Read an image
     *
     * @return array
     */
    private function read()
    {
        $id = filter_input(INPUT_GET, 'id');

        // id is required
        if (!$id) {
            return [
                'ping' => 'pong',
            ];
        }

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

        // id is required
        if (!$source) {
            throw new NotProvidedException('SOURCE must be provided in order to get an image path');
        }

        return $this->image->upload($source);
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
        $this->image = new Image();

        // read request
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->echoResponse($this->read());
        }

        // create request
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->echoResponse($this->create());
        }
    }
}