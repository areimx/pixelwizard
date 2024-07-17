<?php

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function redirectTo($location) {
    header("Location: $location");
    die();
}

function htmlEscape($str) {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

function printArr($arr, $dieAfter = false) {
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
    if ($dieAfter) die();
}

function sendResponse($responseMessage, $responseCode = 500, $die = true) {
    http_response_code($responseCode);
    header('Content-Type: application/json');
    echo '{"code": '.$responseCode.', "message": "'.$responseMessage.'"}';
    if ($die !== false) die();
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function exifToGPS($coordinate, $hemisphere) {
    if (is_string($coordinate)) {
      $coordinate = array_map("trim", explode(",", $coordinate));
    }
    for ($i = 0; $i < 3; $i++) {
      $part = explode('/', $coordinate[$i]);
      if (count($part) == 1) {
        $coordinate[$i] = $part[0];
      } else if (count($part) == 2) {
        $coordinate[$i] = floatval($part[0])/floatval($part[1]);
      } else {
        $coordinate[$i] = 0;
      }
    }
    list($degrees, $minutes, $seconds) = $coordinate;
    $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
    return $sign * ($degrees + $minutes/60 + $seconds/3600);
}
