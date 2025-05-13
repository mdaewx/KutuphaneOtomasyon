<?php
// Create a placeholder image for books with no cover
$width = 150;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Set colors
$bgColor = imagecolorallocate($image, 238, 238, 238);
$textColor = imagecolorallocate($image, 102, 102, 102);
$borderColor = imagecolorallocate($image, 200, 200, 200);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Add border
imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);

// Add text
$text = "No Cover";
$font = 4; // Built-in font
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$x = ($width - $textWidth) / 2;
$y = ($height - $textHeight) / 2;
imagestring($image, $font, $x, $y, $text, $textColor);

// Output image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?> 