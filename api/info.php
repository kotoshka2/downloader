<?php
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    response(false, 'Invalid request method.');
}

$url = cleanShellUrl($_GET['url'] ?? '');

if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    response(false, 'Invalid URL provided.');
}


// Prepare yt-dlp command to get JSON metadata
// --skip-download: Don't download video
// --dump-json: Output JSON metadata
// --no-warnings: Suppress warnings
$cookies = getCookiesFlag();
$escapedUrl = escapeshellarg($url);
$cmd = "yt-dlp --skip-download --dump-json --no-warnings$cookies $escapedUrl 2>&1";



$output = [];
$returnVar = 0;

exec($cmd, $output, $returnVar);

if ($returnVar !== 0 || empty($output)) {
    // Filter out some common non-JSON noise if any
    $errorMsg = !empty($output) ? implode(' ', $output) : 'yt-dlp failed with code ' . $returnVar;
    response(false, 'Failed to fetch video information: ' . cleanInput($errorMsg));
}



// Initialize variables
$title = '';
$thumbnail = '';
$duration = 0;
$durationString = '';

// Process output, sometimes yt-dlp returns multiple JSON objects (e.g. playlist)
// We take the first valid one if possible, or handling single video
foreach ($output as $line) {
    if ($json = json_decode($line, true)) {
        if (isset($json['title'])) {
            $title = $json['title'];
            $thumbnail = $json['thumbnail'] ?? '';
            $duration = $json['duration'] ?? 0;
            $durationString = $json['duration_string'] ?? gmdate("H:i:s", $duration);
            break; // Found first valid video
        }
    }
}

if (empty($title)) {
    $rawOutput = !empty($output) ? implode(' ', $output) : 'No output from yt-dlp';
    response(false, 'Could not parse video info. Raw output: ' . cleanInput($rawOutput));
}


response(true, 'Video information fetched successfully.', [
    'title' => $title,
    'thumbnail' => $thumbnail,
    'duration' => $durationString
]);
?>
