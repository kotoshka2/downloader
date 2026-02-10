<?php
require_once 'utils.php';

header('Content-Type: application/json');

$id = cleanInput($_GET['id'] ?? '');
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

$logFile = getLogPath($id);

if (!file_exists($logFile)) {
    // It might be too early, or it failed to start.
    echo json_encode(['success' => true, 'status' => 'starting', 'progress' => 0]);
    exit;
}

if (!is_readable($logFile)) {
    echo json_encode(['success' => true, 'status' => 'starting', 'progress' => 0]);
    exit;
}

$content = @file_get_contents($logFile);
if ($content === false) {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Failed to read log file.']);
    exit;
}


// Check for errors
if (strpos($content, 'ERROR:') !== false) {
    preg_match('/ERROR: (.*)/', $content, $matches);
    $error = $matches[1] ?? 'Unknown error occurred.';
    echo json_encode(['success' => false, 'status' => 'error', 'message' => $error]);
    exit;
}

// Check for progress
// Pattern: [download]  23.5% of
preg_match_all('/\[download\]\s+(\d+\.\d+)%/', $content, $matches);

$progress = 0;
if (!empty($matches[1])) {
    $progress = floatval(end($matches[1]));
}

// Check if complete
if ($progress >= 100) {
    // Check if the actual file exists
    // The pattern is downloads/{id}-Title.ext
    // We search for any file starting with that ID in the downloads folder
    $searchPattern = __DIR__ . "/../downloads/{$id}-*";
    $files = glob($searchPattern);

    // Filter out .part or .ytdl files which indicate incomplete downloads
    $files = array_filter($files, function($f) {
        return !preg_match('/\.(part|ytdl)$/', $f);
    });

    if (!empty($files)) {
        // We found the finished file!
        // Reset array keys after filter
        $files = array_values($files);
        // Logic to ensure it's fully done (size checks? modification time?)
        // Usually if yt-dlp is done writing to log or close to it, and file exists, it's good.
        // However, merging might be happening.
        // Check log for "Merging formats"
        if (strpos($content, '[Merger] Merging formats') !== false && strpos($content, 'Deleting original') === false) {
             // Still merging
             echo json_encode(['success' => true, 'status' => 'processing', 'progress' => 100]);
             exit;
        }

        // We found at least one file.
        // Since we are splitting downloads, we might have multiple (mp4 + m4a).
        // The merging will happen in download.php.
        
        echo json_encode([
            'success' => true, 
            'status' => 'done', 
            'progress' => 100, 
            'download_url' => 'api/download.php?id=' . $id
        ]);
        exit;
    } else {
        // High progress but file not ready (maybe merging or moving)
         echo json_encode(['success' => true, 'status' => 'processing', 'progress' => 100]);
         exit;
    }
} else {
    echo json_encode(['success' => true, 'status' => 'downloading', 'progress' => $progress]);
    exit;
}
?>
