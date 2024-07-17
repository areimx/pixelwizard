<?php

    require "../load.php";
    startSession();

    $allowedImageTypes = array(
        "ExifTypes" => array(
            2, // IMAGETYPE_JPEG
            3  // IMAGETYPE_PNG
        )
    );

    $imageFile = $_FILES["image"];

    if (!isset($imageFile)) {
        sendResponse('No file uploaded.');
    }

    if (filesize($imageFile["tmp_name"]) <= 0) {
        sendResponse('Uploaded file has no contents.');
    }

    $imageType = exif_imagetype($imageFile["tmp_name"]);
    if (!$imageType || !in_array($imageType, $allowedImageTypes["ExifTypes"])) {
        sendResponse('Uploaded file is not an image.');
    }

    $imageExtension = image_type_to_extension($imageType, true);
    $imageFileName = bin2hex(random_bytes(16)) . $imageExtension;
    $imageFilePath = "../uploads/" . $imageFileName;

    move_uploaded_file($imageFile["tmp_name"], $imageFilePath);
    $_SESSION["img_file_name"] = $imageFileName;

    use Intervention\Image\ImageManagerStatic as Image;
    $img = Image::make($imageFilePath);
    $img->orientate();
    $img->fit(1024);
    $img->save("../uploads/cropped/".$imageFileName.".png", 100, 'png');

    sendResponse("Image uploaded successfully.", 201);