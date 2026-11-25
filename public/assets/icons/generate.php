<?php

declare(strict_types=1);

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD unavailable\n");
    exit(1);
}

$logo = imagecreatefrompng(__DIR__ . '/rootsy-mark.png');
if ($logo === false) {
    fwrite(STDERR, "Unable to load Rootsy logo\n");
    exit(1);
}

foreach ([192, 512] as $size) {
    $image = imagecreatetruecolor($size, $size);
    $background = imagecolorallocate($image, 248, 247, 242);
    imagefilledrectangle($image, 0, 0, $size, $size, $background);
    $margin = (int) round($size * 0.08);
    $markSize = $size - ($margin * 2);
    imagecopyresampled($image, $logo, $margin, $margin, 0, 0, $markSize, $markSize, imagesx($logo), imagesy($logo));
    imagepng($image, __DIR__ . "/icon-{$size}.png");
    imagedestroy($image);
}

imagedestroy($logo);
