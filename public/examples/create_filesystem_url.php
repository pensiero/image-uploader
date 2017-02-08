<?php

// everything relative to the application root
chdir(dirname(realpath(__DIR__ . '/..')));

// Composer autoloading
require_once __DIR__ . '/../../vendor/autoload.php';

// init the Image entity
$image = new \ImageUploader\Entity\Image();

// set the save handler
$image->setSaveHandler(new \ImageUploader\SaveHandler\Filesystem());

// set the validators
$image->setValidators(
    new \ImageUploader\Validator\SizeValidator(),
    new \ImageUploader\Validator\DimensionValidator()
);

// set the filters
$image->setFilters(
    new \ImageUploader\Filter\OptimizeFilter(),
    new \ImageUploader\Filter\DimensionFilter()
);

$url = 'https://i.ytimg.com/vi/tntOCGkgt98/maxresdefault.jpg';

// upload the original image (save it as resized)
$response = $image->upload($url, 320, 255);

header('Content-Type: application/json');

echo json_encode($response);

exit();
