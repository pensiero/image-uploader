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
$image->setValidators([
    new \ImageUploader\Validator\SizeValidator(),
    new \ImageUploader\Validator\DimensionValidator(),
]);

$response = $image->read('5896209597abf1-90318852', 400, 0);

header('Content-Type: application/json');

echo json_encode($response);

exit();
