<?php
session_start();

// Generate random string
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_text = substr(str_shuffle($chars), 0, 5);
$_SESSION['captcha_code'] = $captcha_text;

// Create image
$image = imagecreatetruecolor(120, 40);
$bg = imagecolorallocate($image, 240, 240, 240);
$fg = imagecolorallocate($image, 0, 0, 0);
$line = imagecolorallocate($image, 64, 64, 64);

imagefilledrectangle($image, 0, 0, 120, 40, $bg);

// Add noise lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0,120), rand(0,40), rand(0,120), rand(0,40), $line);
}

// Add text
imagestring($image, 5, 30, 10, $captcha_text, $fg);

// Output
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>