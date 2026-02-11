<?php

// Define FFMPEG path dynamically based on OS
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    if (!defined('FFMPEG_PATH')) define('FFMPEG_PATH', 'C:/ffmpeg/bin/ffmpeg.exe');
} else {
    // On Linux/Docker, assume ffmpeg is in the global PATH
    if (!defined('FFMPEG_PATH')) define('FFMPEG_PATH', 'ffmpeg');
}


if (!defined('COOKIES_FILE')) define('COOKIES_FILE', __DIR__ . '/../cookies.txt');

function response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

function cleanShellUrl($url) {
    return trim(stripslashes($url));
}

function getCookiesFlag() {
    if (file_exists(COOKIES_FILE) && is_file(COOKIES_FILE) && is_readable(COOKIES_FILE)) {
        // We use cookies if the file exists. 
        // If a specific site (like YouTube) fails with cookies, the user should remove YouTube cookies from the file.
        return ' --cookies ' . escapeshellarg(COOKIES_FILE);
    }
    return '';
}


function getLogPath($id) {
    return __DIR__ . "/logs/{$id}.log";
}

function getDownloadPattern($id) {
    // Defines where files are saved. We use ID as prefix.
    return __DIR__ . "/../downloads/{$id}-%(title)s.%(ext)s";
}

// Simple garbage collector
function garbageCollect() {
    $downloadDir = __DIR__ . "/../downloads/";
    $logDir = __DIR__ . "/logs/";
    $files = glob($downloadDir . "*");
    $logs = glob($logDir . "*");

    $now = time();
    $timeout = 600; // 10 minutes

    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file) > $timeout)) {
            unlink($file);
        }
    }
    
    foreach ($logs as $file) {
        if (is_file($file) && ($now - filemtime($file) > $timeout)) {
            unlink($file);
        }
    }
}
?>
