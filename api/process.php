<?php
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Invalid request method.');
}

$url = cleanShellUrl($_POST['url'] ?? '');
$quality = cleanInput($_POST['quality'] ?? ''); // Format ID for quality selection

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

// Ensure logs directory exists - suppress warning if it fails to be handled by response
if (!is_dir(__DIR__ . '/logs')) {
    @mkdir(__DIR__ . '/logs', 0755, true);
}

if (!is_writable(__DIR__ . '/logs')) {
    response(false, 'The api/logs directory is not writable. Please check permissions.');
}

// Command execution
// Windows needs different handling than Linux for background processes.
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

// yt-dlp command:
// --newline: Output progress on new lines for easier parsing
// --restrict-filenames: ASCII only filenames
// -o ...: Output template
// 2>&1: Redirect stderr to stdout
$cookies = getCookiesFlag();

// Build format string based on quality selection
if (!empty($quality) && preg_match('/^\d+$/', $quality)) {
    // User selected specific quality: download that video format + best audio
    // yt-dlp will automatically merge them into a single file
    $formatString = "{$quality}+bestaudio[ext=m4a]/bestaudio";
} else {
    // Default: best video and audio as separate files (current behavior)
    $formatString = "bestvideo[ext=mp4],bestaudio[ext=m4a]";
}

$cmd = "yt-dlp -f \"$formatString\" --newline --restrict-filenames$cookies -o \"$outputTemplate\" \"$url\" > \"$logFile\" 2>&1";


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
