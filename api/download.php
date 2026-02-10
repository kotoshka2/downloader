<?php
require_once 'utils.php';

$id = cleanInput($_GET['id'] ?? '');

if (!$id || !preg_match('/^[a-f0-9]+$/', $id)) {
    die('Invalid ID.');
}

$downloadDir = __DIR__ . "/../downloads/";
$mergedFile = $downloadDir . "merged-{$id}.mp4";

// Check if merged file already exists
if (file_exists($mergedFile)) {
    serveFile($mergedFile);
}

// Look for source files
$files = glob($downloadDir . "{$id}-*");
$videoFile = null;
$audioFile = null;

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
        $videoFile = $file;
    } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'm4a') {
        $audioFile = $file;
    }
}

if (!$videoFile || !$audioFile) {
    // If we don't have both, maybe we have a single file (direct download)?
    // Fallback: serve whatever we have if it's just one file
    if (count($files) === 1) {
        serveFile($files[0]);
    }
    die('Source files incomplete. Please try downloading again.');
}

// Perform Merge
// cmd: ffmpeg -i video.mp4 -i audio.m4a -c:v copy -c:a copy merged.mp4
$cmd = sprintf(
    '"%s" -i "%s" -i "%s" -c:v copy -c:a copy "%s" -y 2>&1',
    FFMPEG_PATH,
    $videoFile,
    $audioFile,
    $mergedFile
);

exec($cmd, $output, $returnCode);

if ($returnCode !== 0 || !file_exists($mergedFile)) {
    die('Merge failed. Error: ' . implode("\n", $output));
}

// Clean up parts? Maybe keep them for now or delete.
// unlink($videoFile);
// unlink($audioFile);

serveFile($mergedFile);

function serveFile($filepath) {
    if (!file_exists($filepath)) {
        die('File not found.');
    }
    
    $filename = basename($filepath);
    // Remove the ID prefix for the user
    $userFilename = preg_replace('/^(merged-)?\w+-/', '', $filename);
    if (!$userFilename) $userFilename = $filename;

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $userFilename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    
    // Clear ALL output buffers to avoid memory issues and start sending immediately
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // 1. TRY X-SENDFILE (Highest performance, Apache handles it)
    // Works if mod_xsendfile is enabled
    if (function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules())) {
        header('X-Sendfile: ' . realpath($filepath));
        exit;
    }

    // 2. FALLBACK: OPTIMIZED CHUNKED READING
    // Explicitly disable compression for this download if possible
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', 1);
    }
    @ini_set('zlib.output_compression', 'Off');

    $file = fopen($filepath, 'rb');
    if ($file) {
        $chunkSize = 1024 * 128; // 128 KB chunks as suggested
        while (!feof($file)) {
            echo fread($file, $chunkSize);
            flush();
            // Check if connection is still alive
            if (connection_status() != 0) {
                fclose($file);
                exit;
            }
        }
        fclose($file);
    }
    exit;
}


?>
