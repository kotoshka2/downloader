<?php
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    response(false, 'Invalid request method.');
}

$url = cleanShellUrl($_GET['url'] ?? '');

if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    response(false, 'Invalid URL provided.');
}

// Get list of all available formats using yt-dlp
$cookies = getCookiesFlag();
$escapedUrl = escapeshellarg($url);
$cmd = "yt-dlp -F --no-warnings$cookies $escapedUrl 2>&1";

$output = [];
$returnVar = 0;

exec($cmd, $output, $returnVar);

if ($returnVar !== 0) {
    $errorMsg = !empty($output) ? implode(' ', $output) : 'Failed to fetch formats';
    if (strlen($errorMsg) > 500) {
        $errorMsg = substr($errorMsg, 0, 500) . '... [truncated]';
    }
    response(false, 'Failed to fetch video formats: ' . cleanInput($errorMsg));
}

// Parse the format list
// Format line looks like: "137          mp4   1920x1080  1080p 4433k , avc1.640028, 30fps, video only"
$videoFormats = [];
$audioFormats = [];

foreach ($output as $line) {
    // Skip header lines and non-format lines
    if (strpos($line, 'ID') !== false || empty(trim($line))) {
        continue;
    }
    
    // Match format lines: ID, EXT, RESOLUTION, other info
    // Example: "137          mp4   1920x1080  1080p 4433k"
    if (preg_match('/^(\d+)\s+(\w+)\s+(?:(\d+)x(\d+))?\s+(?:(\d+)p)?/i', trim($line), $matches)) {
        $formatId = $matches[1];
        $ext = $matches[2];
        $width = isset($matches[3]) ? (int)$matches[3] : 0;
        $height = isset($matches[4]) ? (int)$matches[4] : 0;
        
        // Check if it's video only (contains "video only")
        $isVideoOnly = stripos($line, 'video only') !== false;
        
        // Check if it's audio only (contains "audio only")
        $isAudioOnly = stripos($line, 'audio only') !== false;
        
        // Extract FPS if available
        $fps = 0;
        if (preg_match('/(\d+)fps/', $line, $fpsMatch)) {
            $fps = (int)$fpsMatch[1];
        }
        
        // Extract filesize if available
        $filesizeApprox = '';
        if (preg_match('/(\d+(?:\.\d+)?)(K|M|G)iB/', $line, $sizeMatch)) {
            $filesizeApprox = $sizeMatch[1] . $sizeMatch[2] . 'B';
        }
        
        // Accept MP4 and WEBM video formats with resolution (not just mp4)
        if (($ext === 'mp4' || $ext === 'webm') && $height > 0 && $isVideoOnly) {
            // Check if we already have this quality (keep the best one for each resolution)
            // Prefer mp4 over webm for same resolution
            $qualityKey = $height;
            if (!isset($videoFormats[$qualityKey])) {
                $videoFormats[$qualityKey] = [
                    'format_id' => $formatId,
                    'height' => $height,
                    'width' => $width,
                    'fps' => $fps,
                    'filesize_approx' => $filesizeApprox,
                    'ext' => $ext
                ];
            } elseif ($ext === 'mp4' && $videoFormats[$qualityKey]['ext'] === 'webm') {
                // Replace webm with mp4 if available
                $videoFormats[$qualityKey] = [
                    'format_id' => $formatId,
                    'height' => $height,
                    'width' => $width,
                    'fps' => $fps,
                    'filesize_approx' => $filesizeApprox,
                    'ext' => $ext
                ];
            } elseif ($fps > ($videoFormats[$qualityKey]['fps'] ?? 0) && $ext === $videoFormats[$qualityKey]['ext']) {
                // Keep higher fps for same format type
                $videoFormats[$qualityKey] = [
                    'format_id' => $formatId,
                    'height' => $height,
                    'width' => $width,
                    'fps' => $fps,
                    'filesize_approx' => $filesizeApprox,
                    'ext' => $ext
                ];
            }
        }
        
        // Collect audio formats (prefer m4a)
        if ($isAudioOnly) {
            $audioFormats[] = [
                'format_id' => $formatId,
                'ext' => $ext,
                'line' => $line
            ];
        }
    }
}

if (empty($videoFormats)) {
    // Better error message for debugging
    $allFormatExts = [];
    foreach ($output as $line) {
        if (preg_match('/^(\d+)\s+(\w+)/', trim($line), $m)) {
            $allFormatExts[] = $m[2];
        }
    }
    $uniqueExts = array_unique($allFormatExts);
    $extList = implode(', ', $uniqueExts);
    
    response(false, 'No compatible video formats found. This video might not support quality selection. Found extensions: ' . $extList . '. Try using cookies.txt or check if video requires authentication.');
}

// Sort video formats by height (descending - best quality first)
krsort($videoFormats);

// Find best audio format (prefer m4a, then best available)
$bestAudioFormat = null;
foreach ($audioFormats as $audio) {
    if ($audio['ext'] === 'm4a') {
        $bestAudioFormat = $audio['format_id'];
        break;
    }
}
// If no m4a found, use first audio format
if (!$bestAudioFormat && !empty($audioFormats)) {
    $bestAudioFormat = $audioFormats[0]['format_id'];
}

// Format the response with quality labels
$formattedFormats = [];
foreach ($videoFormats as $format) {
    $height = $format['height'];
    
    // Create quality label
    $qualityLabel = '';
    if ($height >= 2160) {
        $qualityLabel = '4K (2160p)';
    } elseif ($height >= 1440) {
        $qualityLabel = '2K (1440p)';
    } elseif ($height >= 1080) {
        $qualityLabel = '1080p';
    } elseif ($height >= 720) {
        $qualityLabel = '720p';
    } elseif ($height >= 480) {
        $qualityLabel = '480p';
    } elseif ($height >= 360) {
        $qualityLabel = '360p';
    } else {
        $qualityLabel = $height . 'p';
    }
    
    // Add FPS info if 60fps
    if ($format['fps'] >= 60) {
        $qualityLabel .= ' 60fps';
    }
    
    $formattedFormats[] = [
        'quality_label' => $qualityLabel,
        'height' => $height,
        'format_id' => $format['format_id'],
        'fps' => $format['fps'],
        'filesize_approx' => $format['filesize_approx']
    ];
}

response(true, 'Formats fetched successfully.', [
    'formats' => $formattedFormats,
    'audio_format' => $bestAudioFormat
]);
?>
