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

$parsedJson = null;
foreach ($output as $line) {
    if ($json = json_decode($line, true)) {
        if (isset($json['title'])) {
            $parsedJson = $json;
            break; 
        }
    }
}

if (!$parsedJson) {
    $errorMsg = !empty($output) ? implode(' ', $output) : 'yt-dlp failed with code ' . $returnVar;
    // Limit error message length to avoid breaking the UI/JSON response
    if (strlen($errorMsg) > 500) {
        $errorMsg = substr($errorMsg, 0, 500) . '... [truncated]';
    }
    response(false, 'Failed to fetch video information: ' . cleanInput($errorMsg));
}

// Initialize variables from parsed data
$title = $parsedJson['title'] ?? '';
$thumbnail = $parsedJson['thumbnail'] ?? '';
$duration = $parsedJson['duration'] ?? 0;
$durationString = $parsedJson['duration_string'] ?? gmdate("H:i:s", $duration);



response(true, 'Video information fetched successfully.', [
    'title' => $title,
    'thumbnail' => $thumbnail,
    'duration' => $durationString
]);
?>
