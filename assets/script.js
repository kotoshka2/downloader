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

    // Quality Selection Elements
    const qualitySelection = document.getElementById('qualitySelection');
    const qualityLoading = document.getElementById('qualityLoading');
    const qualityOptions = document.getElementById('qualityOptions');

    let debounceTimer;
    let availableFormats = null;
    let selectedQuality = null;

    urlInput.addEventListener('input', () => {
        const url = urlInput.value.trim();
        clearTimeout(debounceTimer);

        // Hide everything if empty
        if (!url) {
            videoPreview.classList.add('d-none');
            previewLoading.classList.add('d-none');
            qualitySelection.classList.add('d-none');
            qualityLoading.classList.add('d-none');
            selectedQuality = null;
            availableFormats = null;
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

                // Fetch available formats after preview loads
                fetchFormats(url);
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
            showError("Network or Server error occurred while fetching video info.");
        }

    }

    async function fetchFormats(url) {
        try {
            // Show loading skeleton
            qualitySelection.classList.add('d-none');
            qualityLoading.classList.remove('d-none');

            const response = await fetch(`api/formats.php?url=${encodeURIComponent(url)}`);
            const data = await response.json();

            if (data.success && data.formats && data.formats.length > 0) {
                availableFormats = data;
                renderQualityOptions(data.formats);
                // Auto-select best quality (first in array)
                selectedQuality = data.formats[0].format_id;

                qualityLoading.classList.add('d-none');
                qualitySelection.classList.remove('d-none');
            } else {
                // No formats available or error - hide quality selection
                qualityLoading.classList.add('d-none');
                qualitySelection.classList.add('d-none');
                // Optionally show a message that quality selection is not available
            }

        } catch (error) {
            console.error("Error fetching formats:", error);
            qualityLoading.classList.add('d-none');
            qualitySelection.classList.add('d-none');
        }
    }

    function renderQualityOptions(formats) {
        qualityOptions.innerHTML = '';

        formats.forEach((format, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-light text-start d-flex justify-content-between align-items-center';

            // Mark first option as selected by default
            if (index === 0) {
                button.classList.add('active');
            }

            const label = document.createElement('span');
            label.textContent = format.quality_label;
            label.className = 'fw-semibold';

            const sizeInfo = document.createElement('small');
            sizeInfo.className = 'text-muted';
            sizeInfo.textContent = format.filesize_approx || '';

            button.appendChild(label);
            if (format.filesize_approx) {
                button.appendChild(sizeInfo);
            }

            button.addEventListener('click', () => {
                // Remove active class from all buttons
                qualityOptions.querySelectorAll('.btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                // Add active class to clicked button
                button.classList.add('active');
                // Update selected quality
                selectedQuality = format.format_id;
            });

            qualityOptions.appendChild(button);
        });
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

            // Add quality parameter if selected
            if (selectedQuality) {
                formData.append('quality', selectedQuality);
            }

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
