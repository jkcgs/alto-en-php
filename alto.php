<?php

$size = 500;
if (isset($_GET['size']) && is_numeric($_GET['size'])) {
    $get_size = intval($_GET['size']);
    if ($get_size >= 100 && $get_size <= 2000) {
        $size = $get_size;
    }
}

$padding = 0;
if (isset($_GET['padding']) && is_numeric($_GET['padding'])) {
    $get_padding = intval($_GET['padding']);
    if ($get_padding >= 0 && $get_padding <= round($size/4)) {
        $padding = $get_padding;
    }
}

$text = "?";
if (isset($_GET['text'])) {
    $text = strtoupper(preg_replace("/[^\w -]/", "", $_GET['text']));
}

//////////////////////////////////////

putenv('GDFONTPATH=' . realpath('.'));
$font = 'arimo';

function reduce($o, $st) {
    for ($i = 0, $j = 0, $rx = true; $i < 16; $i+=2, $j++) {
        $o[$i]   += ($rx ? round($st/3) : $st) * ($j == 0 || $j >= 5 ? 1 : -1);
        $o[$i+1] += ($rx ? $st : round($st/3)) * ($j <= 2 || $j == 7 ? 1 : -1);
        $rx = $j % 2 ? !$rx : $rx;
    }

    return $o;
}

function center_text($text, $font_size, $width) {
    global $font;
    $box = imagettfbbox($font_size, 0, $font, $text);
    $box_width = $box[2] - $box[0];
    return round(($width - $box_width) / 2);
}

function draw_centered($text, $fs_factor, $y_factor) {
    global $im;
    global $size;
    global $sp;
    global $im_fg;
    global $font;

    $fs = round(($size-$sp) * $fs_factor) + round($sp/2);
    $fx = center_text($text, $fs, $size - $sp) + round($sp/2);
    $fy = round(($size-$sp)*$y_factor) + round($sp/2);

    imagettftext($im, $fs, 0, $fx, $fy , $im_fg, $font, $text);
}

$sp = $padding;
$s = floor(($size - ($sp * 2)) / 3);
$octa = [
    $sp + $s, $sp,
    $sp + ($s*2), $sp,
    $sp + ($s*3), $sp + $s,
    $sp + ($s*3), $sp + ($s*2),
    $sp + ($s*2), $sp + ($s*3),
    $sp + $s, $sp + ($s*3),
    $sp, $sp + ($s*2),
    $sp, $sp + $s
];

$im = imagecreatetruecolor($size, $size);
imagesavealpha($im, true);
imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
$im_bg = imagecolorallocate($im, 34, 34, 34);
$im_fg = imagecolorallocate($im, 255, 255, 255);
imagefilledpolygon($im, $octa, 8, $im_bg);

$x = $size - $sp;
$d = [0.006, 0.04, 0.01, 0.04];
$t = true;
foreach($d as $lel) {
    $octa = reduce($octa, round($x * $lel));
    imagefilledpolygon($im, $octa, 8, $t ? $im_fg : $im_bg);
    $t = !$t;
}

//////////////////////////////////////////////////
// Lo anterior se reduce a esto:

draw_centered("ALTO EN", 0.08, 0.45);
draw_centered($text, 0.08, 0.57);
draw_centered("Ministerio de", 0.04, 0.78);
draw_centered("Salud", 0.04, 0.84);

/////////////////////////////////////////////////

ob_start();
imagepng($im);
$x = ob_get_contents();
ob_end_clean();

$imsize = strlen($x);
header("Content-type: image/png");
header("Content-Disposition: inline; filename=\"alto_en_xd.png\"");
header("Content-Length: $imsize");
echo $x;
