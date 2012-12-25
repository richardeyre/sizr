<?php

/*
 * PHP Sizr
 * 
 * Resize and format an image according to instructions passed via the URL.
 */

// Include the library class.
include 'library/sizr.php';

$params = array_merge(
    array(
        'width'  => null,
        'height' => null,
        'src' => Sizr::NO_IMAGE
    ),
    $_GET
);

$image = new Sizr();
$image->setSrc($params['src']);
$image->setWidth($params['width']);
$image->setHeight($params['height']);

$image->output();
