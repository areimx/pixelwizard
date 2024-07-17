<?php

    require "../load.php";
    use Intervention\Image\ImageManagerStatic as Image;
    
    startSession();
    if (empty($_SESSION["img_file_name"]))
        sendResponse("Not authorized.", 401);

    $imgFileName = $_SESSION["img_file_name"];
    $imgFilePath = "../uploads/" . $imgFileName;

    if (!file_exists($imgFilePath))
        sendResponse("Image not found.");

    $img = new Imagick($imgFilePath);
    $profiles = $img->getImageProfiles("icc", true);
    $img->stripImage();
    if(!empty($profiles))
        $img->profileImage("icc", $profiles['icc']);

    $mimeType = $img->getImageMimeType();
    header("Content-Type: $mimeType");

    ob_start();
    echo $img->getImageBlob();
    ob_get_flush();

    $img->destroy();
    die();