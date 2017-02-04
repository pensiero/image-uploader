<?php
namespace ImageUploader\Controller;

use ImageUploader\Entity\Image;
use ImageUploader\SaveHandler\Filesystem;

class Expose
{
    const HEADER_TIME_OFFSET = 60 * 60 * 24 * 30;

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

    public function init()
    {
        // init the Image entity
        $this->image->setSaveHandler(new Filesystem());

        // read request
        if (!$_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING) ?: null;
        $width = filter_input(INPUT_GET, 'width', FILTER_SANITIZE_NUMBER_INT) ?: null;
        $height = filter_input(INPUT_GET, 'height', FILTER_SANITIZE_NUMBER_INT) ?: null;

        $data = $this->image->read($id, $width, $height);

        header("Content-Type: image/jpeg");
        header("Cache-control: Public");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + self::HEADER_TIME_OFFSET));

        readfile($data['local_path']);

        exit();
    }
}