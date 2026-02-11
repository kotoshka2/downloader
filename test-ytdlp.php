<?php
// Test script to check if yt-dlp works on the server
header('Content-Type: text/plain');

echo "=== YT-DLP Test Script ===\n\n";

// Test 1: Check if yt-dlp exists
echo "1. Checking if yt-dlp exists...\n";
$ytdlpPath = trim(shell_exec('which yt-dlp 2>&1'));
echo "Path: $ytdlpPath\n";

if (empty($ytdlpPath)) {
    echo "ERROR: yt-dlp not found in PATH\n";
    exit;
} else {
    echo "OK: yt-dlp found\n\n";
}

// Test 2: Check yt-dlp version
echo "2. Checking yt-dlp version...\n";
$version = shell_exec('yt-dlp --version 2>&1');
echo "Version: $version\n";

// Test 3: Try to fetch formats for a test video
echo "3. Testing format fetching...\n";
$testUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
echo "Test URL: $testUrl\n\n";

$cmd = "yt-dlp -F --no-warnings " . escapeshellarg($testUrl) . " 2>&1";
echo "Command: $cmd\n\n";

echo "Output:\n";
echo "---\n";
$output = shell_exec($cmd);
echo $output;
echo "\n---\n";

if (empty($output)) {
    echo "\nERROR: No output from yt-dlp\n";
} else {
    echo "\nSUCCESS: yt-dlp returned data\n";
}

echo "\n=== Test Complete ===\n";
?>
