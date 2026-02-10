# CI/CD with GitHub Actions

This project includes a pre-configured GitHub Actions workflow that automatically builds and publishes your Docker image whenever you push to the `main` branch.

## How it Works
The workflow file is located at `.github/workflows/docker-publish.yml`.
1.  **Trigger**: Runs on every push to `main`.
2.  **Build**: Builds the Docker image from your `Dockerfile`.
3.  **Push**: Pushes the image to **GitHub Container Registry (GHCR)**.

## Usage Guide

### 1. Enable Feature
Ensure "Actions" are enabled in your GitHub repository settings.

### 2. Push Code
Simply push your code to GitHub:
```bash
git add .
git commit -m "Added CI/CD"
git push origin main
```

### 3. Pulling the Image
Once the workflow finishes, you can pull your image from anywhere using docker:
```bash
# Log in first (using your GitHub username and a Personal Access Token)
echo $CR_PAT | docker login ghcr.io -u USERNAME --password-stdin

# Pull the image
docker pull ghcr.io/USERNAME/REPO_NAME:latest
```

### 4. Deploying
On your server, you can run the latest image using:
```bash
docker run -d -p 8080:80 \
  -v $(pwd)/downloads:/var/www/html/downloads \
  ghcr.io/USERNAME/REPO_NAME:latest
```

## Customization
To use **Docker Hub** instead of GHCR:
1.  Change `registry: ghcr.io` to `registry: docker.io` in the yaml file.
2.  Set `DOCKER_USERNAME` and `DOCKER_PASSWORD` in your repo defaults (Settings -> Secrets -> Actions).
