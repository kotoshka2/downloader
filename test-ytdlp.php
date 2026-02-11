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
$version = shell_exec('yt-dlp --no-config --version 2>&1');
echo "Version: $version\n";

// Test 3: Try to fetch formats
echo "3. Testing fetching information...\n";
$testUrl = 'https://www.youtube.com/watch?v=9_ldCQUgU7Q';
echo "Test URL: $testUrl\n\n";

echo "--- A: WITHOUT COOKIES ---\n";
$cmdA = "yt-dlp --no-config --dump-json --no-warnings " . escapeshellarg($testUrl) . " 2>&1";
$outputA = shell_exec($cmdA);
echo (json_decode($outputA) ? "SUCCESS: Got JSON metadata" : "ERROR: " . substr($outputA, 0, 200));
echo "\n\n";

echo "--- B: WITH COOKIES ---\n";
$cookies = file_exists(__DIR__ . '/cookies.txt') ? " --cookies cookies.txt" : "";
if (empty($cookies)) {
    echo "Skipping: cookies.txt not found in " . __DIR__;
} else {
    $cmdB = "yt-dlp --no-config --dump-json --no-warnings$cookies " . escapeshellarg($testUrl) . " 2>&1";
    $outputB = shell_exec($cmdB);
    echo (json_decode($outputB) ? "SUCCESS: Got JSON metadata" : "ERROR: " . substr($outputB, 0, 200));
}

echo "\n\n=== Test Complete ===\n";
?>
