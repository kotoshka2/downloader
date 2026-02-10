document.addEventListener('DOMContentLoaded', () => {
    const downloadBtn = document.getElementById('downloadBtn');
    const urlInput = document.getElementById('videoUrl');
    const statusArea = document.getElementById('statusArea');
    const statusText = document.getElementById('statusText');
    const percentageText = document.getElementById('percentageText');
    const progressBar = document.getElementById('progressBar');
    const resultArea = document.getElementById('resultArea');
    const errorArea = document.getElementById('errorArea');

    // Preview Elements
    const videoPreview = document.getElementById('videoPreview');
    const previewLoading = document.getElementById('previewLoading');
    const previewThumb = document.getElementById('previewThumb');
    const previewTitle = document.getElementById('previewTitle');
    const previewDuration = document.getElementById('previewDuration');

    let debounceTimer;

    urlInput.addEventListener('input', () => {
        const url = urlInput.value.trim();
        clearTimeout(debounceTimer);

        // Hide everything if empty
        if (!url) {
            videoPreview.classList.add('d-none');
            previewLoading.classList.add('d-none');
            return;
        }

        // Show loading immediately for better feedback
        videoPreview.classList.add('d-none');
        previewLoading.classList.remove('d-none');

        // Debounce API call
        debounceTimer = setTimeout(() => {
            fetchVideoInfo(url);
        }, 800);
    });

    async function fetchVideoInfo(url) {
        try {
            const response = await fetch(`api/info.php?url=${encodeURIComponent(url)}`);
            const data = await response.json();

            if (data.success) {
                previewThumb.src = data.thumbnail;
                previewTitle.textContent = data.title;
                previewDuration.textContent = data.duration;

                previewLoading.classList.add('d-none');
                videoPreview.classList.remove('d-none');
                errorArea.classList.add('d-none'); // Hide error if valid
            } else {
                // If invalid URL or error, hide everything
                previewLoading.classList.add('d-none');
                videoPreview.classList.add('d-none');
                if (data.message) {
                    showError(data.message);
                }
            }

        } catch (error) {
            console.error("Error fetching video info:", error);
            previewLoading.classList.add('d-none');
            videoPreview.classList.add('d-none');
        }
    }

    let pollInterval;

    downloadBtn.addEventListener('click', async () => {
        const url = urlInput.value.trim();
        if (!url) {
            showError("Please enter a valid URL.");
            return;
        }

        // Reset UI
        resetUI();
        setLoading(true);
        statusArea.classList.remove('d-none');
        statusText.textContent = "Initializing...";

        try {
            // Step 1: Request Processing
            const formData = new FormData();
            formData.append('url', url);

            const response = await fetch('api/process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || "Failed to start download.");
            }

            const downloadId = data.id;

            // Step 2: Start Polling
            pollProgress(downloadId);

        } catch (error) {
            console.error(error);
            showError(error.message);
            setLoading(false);
            statusArea.classList.add('d-none');
        }
    });

    function pollProgress(id) {
        pollInterval = setInterval(async () => {
            try {
                const response = await fetch(`api/progress.php?id=${id}`);
                const data = await response.json();

                if (data.status === 'downloading') {
                    const percent = data.progress || 0;
                    updateProgress(percent, "Downloading...");
                } else if (data.status === 'processing') {
                    updateProgress(100, "Processing video...");
                    progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');
                } else if (data.status === 'done') {
                    clearInterval(pollInterval);
                    completeDownload(data);
                } else if (data.status === 'error') {
                    clearInterval(pollInterval);
                    throw new Error(data.message || "An error occurred during download.");
                }
            } catch (error) {
                clearInterval(pollInterval);
                showError(error.message);
                setLoading(false);
            }
        }, 1000);
    }

    function updateProgress(percent, text) {
        progressBar.style.width = `${percent}%`;
        percentageText.textContent = `${percent}%`;
        statusText.textContent = text;
    }

    function completeDownload(data) {
        setLoading(false);
        // Ensure progress bar is full
        progressBar.style.width = '100%';
        percentageText.textContent = '100%';
        statusText.textContent = "Complete!";

        resultArea.classList.remove('d-none');
        const downloadUrl = data.download_url;

        resultArea.innerHTML = `
            <a href="${downloadUrl}" class="btn btn-success btn-lg shadow animate__animated animate__pulse">
                <i class="fas fa-file-download me-2"></i> Download File
            </a>
            <div class="mt-2 text-muted small">File ready. Expires in 10 minutes.</div>
        `;
    }

    function showError(msg) {
        errorArea.textContent = msg;
        errorArea.classList.remove('d-none');
    }

    function resetUI() {
        errorArea.classList.add('d-none');
        resultArea.classList.add('d-none');
        statusArea.classList.add('d-none');
        progressBar.style.width = '0%';
        percentageText.textContent = '0%';
        resultArea.innerHTML = '';
    }

    function setLoading(isLoading) {
        downloadBtn.disabled = isLoading;
        if (isLoading) {
            downloadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
        } else {
            downloadBtn.innerHTML = 'Start Download <i class="fas fa-download ms-2"></i>';
        }
    }
});
