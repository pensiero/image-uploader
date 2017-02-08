<?php declare(strict_types=1);
namespace ImageUploader\Controller;

use ImageUploader\Entity\Image;
use ImageUploader\Exception\NotProvidedException;
use ImageUploader\Filter\DimensionFilter;
use ImageUploader\Filter\OptimizeFilter;
use ImageUploader\SaveHandler\Filesystem;
use ImageUploader\SaveHandler\Flysystem;
use ImageUploader\Validator\SizeValidator;
use ImageUploader\Validator\DimensionValidator;

class Expose
{
    const HEADER_TIME_OFFSET = 60 * 60 * 24 * 30;

    /**
     * @var Image
     */
    private $image;

    /**
     * Expose constructor.
     */
    public function __construct()
    {
        // init the Image entity
        $this->image = new Image();

        // check SAVE_HANDLER env var
        if (!getenv('SAVE_HANDLER') || !in_array(getenv('SAVE_HANDLER'), ['filesystem', 'aws'])) {
            throw new NotProvidedException('SAVE_HANDLER must be provided and could be "filesystem" or "aws"');
        }

        // set the save handler
        switch (getenv('SAVE_HANDLER')) {
            case 'filesystem': {
                $saveHandler = new Filesystem();
            }
            break;
            case 'aws': {
                $saveHandler = new Flysystem(new Flysystem\Adapter\AwsAdapter());
            }
            break;
            default: {
                $saveHandler = new Filesystem();
            }
            break;
        }

        $this->image->setSaveHandler($saveHandler);

        // set the validators
        $this->image->setValidators(
            new SizeValidator(),
            new DimensionValidator()
        );

        // set the filters
        $this->image->setFilters(
            new OptimizeFilter(),
            new DimensionFilter()
        );
    }

    /**
     * Expose the image
     */
    public function init()
    {
        // read request
        if (!$_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING) ?: null;
        $width = (int) filter_input(INPUT_GET, 'width', FILTER_SANITIZE_NUMBER_INT) ?: null;
        $height = (int) filter_input(INPUT_GET, 'height', FILTER_SANITIZE_NUMBER_INT) ?: null;

        $data = $this->image->read($id, $width, $height);
        if ($data['status_code'] !== 200) {
            echo $data['message'];
            exit();
        }

        header("Content-Type: image/jpeg");
        header("Cache-control: Public");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + self::HEADER_TIME_OFFSET));

        // echo the image blob
        echo $this->image->getBlob();

        exit();
    }
}