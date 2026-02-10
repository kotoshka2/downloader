<?php $pageTitle = "Скачать Instagram Stories"; include 'includes/header.php'; ?>

<div class="row w-100 justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="glass-card p-5">
            <div class="text-center mb-4">
                <h1 class="fw-bold mb-2">Instagram Stories</h1>
                <p class="text-white">Скачивайте сторис из Instagram в лучшем качестве анонимно и быстро.</p>
            </div>

            <form id="downloadForm">
                <div class="mb-3 input-group input-group-lg">
                    <span class="input-group-text bg-transparent border-end-0 text-secondary">
                        <i class="fas fa-link"></i>
                    </span>
                    <input type="url" class="form-control border-start-0 ps-0" id="videoUrl" placeholder="Вставьте ссылку на сторис..." required>
                </div>
                <button type="button" id="downloadBtn" class="btn btn-primary w-100 btn-lg shadow-sm">
                    Начать загрузку <i class="fas fa-download ms-2"></i>
                </button>
                <div class="mt-3 text-center">
                    <p class="disclaimer-text mb-0">
                        Убедитесь, что вы не нарушаете права других людей загружаемыми файлами. <br>
                        Защищенную авторским правом музыку нельзя скачивать с помощью этого инструмента.
                    </p>
                </div>
            </form>

            <div id="videoPreview" class="mt-4 mb-4 d-none">
                <div class="card bg-transparent border-0">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="position-relative">
                                <img id="previewThumb" src="" alt="Thumbnail" class="img-fluid rounded shadow-sm w-100 object-fit-cover" style="height: 120px;">
                                <span id="previewDuration" class="position-absolute bottom-0 end-0 bg-dark text-white px-2 py-1 m-1 rounded small opacity-75" style="font-size: 0.75rem;">00:00</span>
                            </div>
                        </div>
                        <div class="col-md-7"><h6 id="previewTitle" class="card-title fw-bold mb-1 text-truncate-2 text-white">Video Title</h6></div>
                    </div>
                </div>
            </div>
            <div id="previewLoading" class="mt-4 mb-4 d-none">
                <div class="card bg-transparent border-0/50">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5"><div class="skeleton-box rounded" style="height: 120px; width: 100%;"></div></div>
                        <div class="col-md-7">
                            <div class="skeleton-box rounded mb-2" style="height: 20px; width: 80%;"></div>
                            <div class="skeleton-box rounded" style="height: 20px; width: 60%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="statusArea" class="mt-4 d-none">
                <div class="d-flex justify-content-between mb-1">
                    <span id="statusText" class="small fw-semibold">Обработка...</span>
                    <span id="percentageText" class="small fw-bold">0%</span>
                </div>
                <div class="progress" role="progressbar" style="height: 8px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-gradient" style="width: 0%"></div>
                </div>
            </div>
            <div id="resultArea" class="mt-4 d-none text-center"></div>
            <div id="errorArea" class="mt-3 alert alert-danger d-none" role="alert"></div>
        </div>
    </div>
</div>

<?php include 'includes/instructions.php'; ?>

<?php include 'includes/footer.php'; ?>
