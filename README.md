# PHP YouTube Downloader

A simple, secure, and functional PHP-based web application for downloading videos from YouTube (or similar platforms) using `yt-dlp`.

## Features
- **Simple UI**: Clean interface with a URL input and progress indicator.
- **Backend**: Uses PHP and `yt-dlp` for robust video downloading.
- **Progress Tracking**: Real-time feedback on download status.
- **Security**: Input validation and temporary file storage.

## Requirements
- **PHP 8.0+**
- **Web Server**: Apache (with `mod_rewrite` recommended) or Nginx.
- **System Tools**:
    - `yt-dlp`: Must be installed and accessible in the system PATH.
    - `ffmpeg`: Required for merging video and audio streams (best quality downloads).
    - `python3`: Required by `yt-dlp`.

## Installation

### 1. Install Dependencies (Windows Example)
Ensure you have `yt-dlp` and `ffmpeg` installed.
- **yt-dlp**: Download the executable from [GitHub](https://github.com/yt-dlp/yt-dlp/releases) and add it to your System PATH or place it in the project root.
- **FFmpeg**: Download from [ffmpeg.org](https://ffmpeg.org/download.html) and add `bin` folder to System PATH.

### 2. Configure PHP
Ensure `shell_exec` and `exec` are enabled in your `php.ini`.
```ini
; disable_functions = ... (ensure exec, shell_exec are NOT listed here)
```

### 3. Deploy
1. Copy the files to your web server's document root (e.g., `htdocs` or `/var/www/html`).
2. Ensure the `downloads` directory is writable by the web server user.

### 4. Permissions
The web server needs permission to:
- Execute `yt-dlp`.
- Write to `downloads/`.
- Write to `api/` (for temporary log files, if applicable, otherwise use system temp).

## Usage
1. Open `index.php` in your browser.
2. Paste a YouTube URL.
3. Click "Download".
4. Wait for processing to complete and click the "Download File" button.

## Disclaimer
This tool is for personal use only. Please respect copyright laws and YouTube's Terms of Service.
