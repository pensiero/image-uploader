<?php

// everything relative to the application root
chdir(dirname(realpath(__DIR__ . '/..')));

// Composer autoloading
require_once __DIR__ . '/../../vendor/autoload.php';

$image = new \ImageUploader\Entity\Image();
$image->setSaveHandler(new \ImageUploader\SaveHandler\Filesystem());

$response = $image->read('https://images.uala.dev/i/2/5/8/8/1/3/0/9/5896209597abf1-90318852_0_0.jpg', 300, 0);

header('Content-Type: application/json');

echo json_encode($response);

exit();
