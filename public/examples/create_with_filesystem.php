<?php

// everything relative to the application root
chdir(dirname(realpath(__DIR__ . '/..')));

// Composer autoloading
require_once __DIR__ . '/../../vendor/autoload.php';

$image = new \ImageUploader\Entity\Image();
$image->setSaveHandler(new \ImageUploader\SaveHandler\Filesystem());

$response = $image->upload('https://i.ytimg.com/vi/tntOCGkgt98/maxresdefault.jpg', 0, 500);

header('Content-Type: application/json');

echo json_encode($response);

exit();
