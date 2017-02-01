<?php

// everything relative to the application root
chdir(dirname(realpath(__DIR__ . '/..')));

// Composer autoloading
require_once __DIR__ . '/../../vendor/autoload.php';

$image = new \ImageUploader\Entity\Image();
$image->setSaveHandler(new \ImageUploader\SaveHandler\Filesystem());

$response = $image->read('58920a94b8d286_75951619');

header('Content-Type: application/json');

echo json_encode($response);

exit();
