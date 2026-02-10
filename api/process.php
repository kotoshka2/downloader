<?php
require_once 'utils.php';

require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Invalid request method.');
}

$url = cleanInput($_POST['url'] ?? '');

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    response(false, 'Invalid URL provided.');
}

// Basic security check to ensure it mimics a video URL (optional but good)
// Allowing broad match for now as yt-dlp supports many sites.

// Trigger GC occasionally
if (rand(1, 10) === 1) {
    garbageCollect();
}

$id = uniqid();
$logFile = getLogPath($id);
$outputTemplate = getDownloadPattern($id);

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Command execution
// Windows needs different handling than Linux for background processes.
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// yt-dlp command:
// --newline: Output progress on new lines for easier parsing
// --restrict-filenames: ASCII only filenames
// -o ...: Output template
// 2>&1: Redirect stderr to stdout
$cmd = "yt-dlp -f \"bestvideo[ext=mp4],bestaudio[ext=m4a]\" --newline --restrict-filenames -o \"$outputTemplate\" \"$url\" > \"$logFile\" 2>&1";

if ($isWindows) {
    // Windows background execution
    try {
        pclose(popen("start /B " . $cmd, "r"));
    } catch (Exception $e) {
         response(false, 'Failed to start download process: ' . $e->getMessage());
    }
} else {
    // Linux background execution
    exec("$cmd &");
}

response(true, 'Download started', ['id' => $id]);
?>
