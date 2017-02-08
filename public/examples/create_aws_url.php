<?php

// everything relative to the application root
chdir(dirname(realpath(__DIR__ . '/..')));

// Composer autoloading
require_once __DIR__ . '/../../vendor/autoload.php';

// init the Image entity
$image = new \ImageUploader\Entity\Image();

// set the save handler
$saveHandler = new \ImageUploader\SaveHandler\Flysystem(new \ImageUploader\SaveHandler\Flysystem\Adapter\AwsAdapter());
$image->setSaveHandler($saveHandler);

// set the validators
$image->setValidators([
    new \ImageUploader\Validator\SizeValidator(),
    new \ImageUploader\Validator\DimensionValidator(),
]);

$url = 'https://i.ytimg.com/vi/tntOCGkgt98/maxresdefault.jpg';

// upload the original image (save it as resized)
$response = $image->upload($url);

header('Content-Type: application/json');

echo json_encode($response);

exit();
