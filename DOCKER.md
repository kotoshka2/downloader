# Docker Deployment Guide

This guide explains how to build and run the YouTube Downloader application using Docker.

## Prerequisites
- Docker and Docker Compose installed on your system.
- If transferring to another machine, Docker must be installed there too.

## Setup & Running (Local)
1.  **Build and Start**
    Run the following command in the project root:
    ```bash
    docker-compose up -d --build
    ```

2.  **Access the Application**
    Open your browser and navigate to:
    [http://localhost:8080](http://localhost:8080)

3.  **Stopping the Container**
    ```bash
    docker-compose down
    ```

## Moving to Another Server (Offline Method)
If you want to run this container on another server without rebuilding it:

### 1. Save the Image
On your current machine, save the built image to a file:
```bash
# Find the image name (usually foldername-service, e.g., youtube-youtube-downloader)
docker save -o youtube-downloader.tar youtube-youtube-downloader
```

### 2. Transfer the File
Copy `youtube-downloader.tar` to your target server (e.g., via SCP, USB, etc.).

### 3. Load the Image
On the target server:
```bash
docker load -i youtube-downloader.tar
```

### 4. Run on Target Server
You can run it using `docker run`:
```bash
docker run -d -p 8080:80 \
  -v $(pwd)/downloads:/var/www/html/downloads \
  -v $(pwd)/api/logs:/var/www/html/api/logs \
  youtube-youtube-downloader
```

## Configuration

### Using Cookies for Authentication
Some sites (Instagram, Facebook, restricted YouTube videos) require cookies to work. If you see errors like "Rate limit reached" or "Login required", follow these steps:

1.  **Install Cookie Extension**
    Install the extension **"Get cookies.txt LOCALLY"** for [Chrome](https://chrome.google.com/webstore/detail/get-cookiestxt-locally/ccmclabjdofocmjdmegebebeonhfcmio) or [Firefox](https://addons.mozilla.org/en-US/firefox/addon/get-cookiestxt/).

2.  **Export Cookies**
    - Go to the site you want to download from (e.g., [instagram.com](https://instagram.com)).
    - Log in to your account.
    - Click the extension icon and select **"Export"** or **"Download"** (Netscape format).
    - Save the file as `cookies.txt` in the root of this project.

3.  **Docker Setup**
    If you are using Docker, you need to "mount" this file into the container.
    - Open `docker-compose.yml` and add the line under `volumes:`:
      ```yaml
      services:
        youtube-downloader:
          # ...
          volumes:
            - ./downloads:/var/www/html/downloads
            - ./api/logs:/var/www/html/api/logs
            - ./cookies.txt:/var/www/html/cookies.txt  # <--- ADD THIS LINE
      ```
    - Restart the containers:
      ```bash
      docker-compose up -d
      ```

4.  **Verification**
    Once the file is placed and mapped, the application will automatically use it for all requests.

---

- **Ports**: The application runs on port `8080` by default. You can change this in `docker-compose.yml`.


- **Volumes**:
    - `./downloads`: Downloaded files are saved here on your host machine.
    - `./api/logs`: Application logs are accessible here.

## Troubleshooting

- **Permissions**: If you encounter permission errors on Linux host (e.g. `Unexpected token <`), ensure the `downloads` and `api/logs` directories on your host are writable by the container user. On Linux, Docker often runs as root, while Apache runs as `www-data` (UID 33).
    ```bash
    # Fix permissions on host
    sudo chown -R 33:33 downloads api/logs
    sudo chmod -R 775 downloads api/logs
    ```

