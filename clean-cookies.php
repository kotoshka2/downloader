<?php
// clean-cookies.php
// Script to clean HTML entities from cookies.txt (fix for production issues)

$cookiesFile = __DIR__ . '/cookies.txt';

if (!file_exists($cookiesFile)) {
    die("Error: cookies.txt not found at: $cookiesFile\n");
}

echo "Reading cookies.txt...\n";
$content = file_get_contents($cookiesFile);

// 1. Декодируем HTML сущности
$cleaned = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$cleaned = str_replace(['&#039;', '&quot;', '&amp;', '&lt;', '&gt;'], ["'", '"', '&', '<', '>'], $cleaned);

// 2. Исправляем Пробелы -> Табы (yt-dlp требует именно табы)
// Если в строке нет табов, но есть группы пробелов - заменяем их
$lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $cleaned));
$fixedLines = [];

foreach ($lines as $line) {
    if (empty(trim($line)) || str_starts_with(trim($line), '#')) {
        $fixedLines[] = $line;
        continue;
    }
    
    // Если в строке нет табов, пробуем заменить двойные+ пробелы на табы
    if (strpos($line, "\t") === false) {
        $line = preg_replace('/\s{2,}/', "\t", $line);
    }
    $fixedLines[] = $line;
}
$cleaned = implode("\n", $fixedLines);
echo "✓ Cleaned cookies.txt written successfully!\n";
echo "Try running your download again now.\n";
?>
