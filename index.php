<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Downloader</title>
    <meta name="description" content="Simple and fast YouTube video downloader.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
    <div class="row w-100 justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="glass-card p-5">
                <div class="text-center mb-4">
                    <h1 class="fw-bold mb-2"><i class="fab fa-youtube text-danger"></i> Video Downloader</h1>
                    <p class="text-muted">Download videos in high quality effortlessly.</p>
                </div>

                <form id="downloadForm">
                    <div class="mb-3 input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-end-0 text-secondary">
                            <i class="fas fa-link"></i>
                        </span>
                        <input type="url" class="form-control border-start-0 ps-0" id="videoUrl" placeholder="Paste video URL here..." required>
                    </div>
                    <button type="button" id="downloadBtn" class="btn btn-primary w-100 btn-lg shadow-sm">
                        Start Download <i class="fas fa-download ms-2"></i>
                    </button>
                </form>

                <!-- Video Preview -->
                <div id="videoPreview" class="mt-4 mb-4 d-none">
                    <div class="card bg-transparent border-0">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <div class="position-relative">
                                    <img id="previewThumb" src="" alt="Video Thumbnail" class="img-fluid rounded shadow-sm w-100 object-fit-cover" style="height: 120px;">
                                    <span id="previewDuration" class="position-absolute bottom-0 end-0 bg-dark text-white px-2 py-1 m-1 rounded small opacity-75" style="font-size: 0.75rem;">00:00</span>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h6 id="previewTitle" class="card-title fw-bold mb-1 text-truncate-2 text-white">Video Title</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Placeholder -->
                <div id="previewLoading" class="mt-4 mb-4 d-none">
                    <div class="card bg-transparent border-0/50">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <div class="skeleton-box rounded" style="height: 120px; width: 100%;"></div>
                            </div>
                            <div class="col-md-7">
                                <div class="skeleton-box rounded mb-2" style="height: 20px; width: 80%;"></div>
                                <div class="skeleton-box rounded" style="height: 20px; width: 60%;"></div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Status & Progress -->
                <div id="statusArea" class="mt-4 d-none">
                    <div class="d-flex justify-content-between mb-1">
                        <span id="statusText" class="small fw-semibold">Processing...</span>
                        <span id="percentageText" class="small fw-bold">0%</span>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Download progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-gradient" style="width: 0%"></div>
                    </div>
                </div>

                <div id="resultArea" class="mt-4 d-none text-center">
                    <!-- Download Link will appear here -->
                </div>

                <div id="errorArea" class="mt-3 alert alert-danger d-none" role="alert">
                </div>

            </div>
            
            <div class="text-center mt-3">
                <p class="small text-secondary opacity-75">
                    For personal use only. Please respect copyright laws.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/script.js"></script>
</body>
</html>
