<?php

// everything relative to the application root
chdir(dirname(realpath(__DIR__ . '/..')));

// Composer autoloading
require_once __DIR__ . '/../../vendor/autoload.php';

$image = new \ImageUploader\Entity\Image();
$image->setSaveHandler(new \ImageUploader\SaveHandler\Filesystem());

$response = $image->read('5896209597abf1-90318852', 400, 0);

header('Content-Type: application/json');

echo json_encode($response);

exit();
