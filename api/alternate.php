<?php
    require "../load.php";

    startSession();
    if (empty($_SESSION["img_file_name"]))
        sendResponse("Not authorized.", 401);

    $imgFileName = $_SESSION["img_file_name"];
    $croppedFilePath = '../uploads/cropped/'.$imgFileName.'.png';
    if (!file_exists($croppedFilePath)) sendResponse("File not found.");

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.openai.com/v1/images/variations',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('image'=> new CURLFILE($croppedFilePath),'n' => '1','size' => '1024x1024'),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . OPENAI_API_KEY
        ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    if (!empty($response["data"][0]["url"])) {
        $altImageUrl = $response["data"][0]["url"];
    }
    sendResponse($altImageUrl, 200);