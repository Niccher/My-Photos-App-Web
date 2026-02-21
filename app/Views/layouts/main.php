<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <script>
        const BASE_URL = '<?= base_url() ?>';
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 0.75rem 1rem;
            z-index: 1040;
        }
        .navbar-brand {
            font-weight: 600;
            color: #5f6368;
            display: flex;
            align-items: center;
        }
        .navbar-brand i {
            color: #4285f4;
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }
        .sidebar {
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding: 1rem 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: white;
            width: 280px;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #5f6368;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link i {
            margin-right: 1.5rem;
            font-size: 1.25rem;
        }
        .sidebar .nav-link.active {
            color: #1a73e8;
            background-color: #e8f0fe;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            transition: all 0.3s;
        }
        .storage-indicator {
            padding: 1.5rem;
            margin-top: auto;
            border-top: 1px solid #eee;
        }
        .progress {
            height: 4px;
            margin-bottom: 0.5rem;
        }

        /* Lightbox Navigation */
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1060;
            background: rgba(0,0,0,0.3);
            border: none;
            color: white;
            padding: 1.5rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
            opacity: 0;
        }
        #lightboxModal:hover .lightbox-nav {
            opacity: 1;
        }
        .lightbox-nav:hover {
            background: rgba(0,0,0,0.6);
            scale: 1.1;
        }
        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }
        
        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: -280px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <button class="btn btn-link d-lg-none me-2" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand" href="<?= base_url() ?>">
            <i class="bi bi-images"></i> Photos
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <button class="btn btn-outline-primary btn-sm" id="btnScan" title="Scan uploads folder">
                <i class="bi bi-arrow-repeat"></i> <span class="d-none d-md-inline">Scan</span>
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal" title="Upload photos">
                <i class="bi bi-cloud-upload"></i> <span class="d-none d-md-inline">Upload</span>
            </button>
            <?php if (auth()->loggedIn()): ?>
            <div class="dropdown">
                <button class="btn btn-link p-0 d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:34px;height:34px;background:linear-gradient(135deg,#4285f4,#00c6ff);font-size:0.85rem;cursor:pointer;">
                        <?= strtoupper(substr(auth()->user()->username ?? auth()->user()->email, 0, 1)) ?>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:200px;">
                    <li class="px-3 py-2">
                        <div class="fw-semibold" style="font-size:0.9rem;"><?= esc(auth()->user()->username ?? '') ?></div>
                        <div class="text-muted" style="font-size:0.78rem;"><?= esc(auth()->user()->email) ?></div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= url_to('logout') ?>">
                            <i class="bi bi-box-arrow-right text-danger"></i>
                            <span>Sign out</span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif ?>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="sidebar">
            <div class="position-sticky d-flex flex-column h-100">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('/')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url() ?>">
                            <span><i class="bi bi-image"></i> Photos</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['photos'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('explore')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('explore') ?>">
                            <span><i class="bi bi-search"></i> Explore</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['explore'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('sharing')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('sharing') ?>">
                            <span><i class="bi bi-people"></i> Sharing</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['sharing'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('favorites')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('favorites') ?>">
                            <span><i class="bi bi-heart"></i> Favorites</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['favorites'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('albums')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('albums') ?>">
                            <span><i class="bi bi-folder2-open"></i> Albums</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['albums'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('analytics')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('analytics') ?>">
                            <span><i class="bi bi-graph-up-arrow"></i> Analytics</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('archive')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('archive') ?>">
                            <span><i class="bi bi-archive"></i> Archive</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['archive'] ?? 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (url_is('trash')) ? 'active' : '' ?> d-flex justify-content-between align-items-center" href="<?= base_url('trash') ?>">
                            <span><i class="bi bi-trash"></i> Trash</span>
                            <span class="badge rounded-pill bg-light text-dark opacity-75 small fw-normal"><?= $counts['trash'] ?? 0 ?></span>
                        </a>
                    </li>
                </ul>
                
                <div class="storage-indicator">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-cloud-check me-2 text-muted"></i>
                        <span class="small text-muted">Storage</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $storagePercent ?? 0 ?>%" aria-valuenow="<?= $storagePercent ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="small text-muted mb-0"><?= $storageUsed ?? '0 B' ?> of 1 GB used</p>
                </div>
            </div>
        </nav>

        <main class="main-content">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<!-- Lightbox Modal (Global) -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0 flex-row">
            <div class="modal-header border-0 p-3 position-absolute top-0 start-0 w-100 d-flex justify-content-between" style="z-index: 1056; background: linear-gradient(to bottom, rgba(0,0,0,0.5), transparent);">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="d-flex align-items-center bg-black rounded px-2" style="opacity: 0.8;">
                    <button type="button" class="btn btn-link text-white p-2" id="btnShareLink" title="Create Public Link">
                        <i class="bi bi-link-45deg fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-link text-white p-2" id="btnFavorite" title="Favorite">
                        <i class="bi bi-heart fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-link text-white p-2" id="btnAddToAlbum" title="Add to Album">
                        <i class="bi bi-plus-circle fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-link text-white p-2" id="btnRestore" style="display: none;" title="Restore">
                        <i class="bi bi-clock-history fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-link text-white p-2" id="btnArchive" title="Archive/Unarchive">
                        <i class="bi bi-archive fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-link text-white p-2" id="btnDelete" title="Delete">
                        <i class="bi bi-trash fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-link text-white p-2 ms-2 border-start border-secondary" id="btnInfo" title="Info">
                        <i class="bi bi-info-circle fs-5"></i>
                    </button>
                </div>
            </div>
            
            <!-- Link Copy Tooltip (Pseudo) -->
            <div id="shareLinkPopup" class="position-absolute top-10 start-50 translate-middle-x bg-white text-dark rounded-pill shadow px-3 py-2 d-none" style="z-index: 1060; margin-top: 60px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold" id="sharedUrlText"></span>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" id="btnCopyLink">Copy</button>
                </div>
            </div>
            <div class="modal-body p-0 d-flex align-items-center justify-content-center flex-grow-1 overflow-hidden" id="lightboxImageContainer">
            </div>

            <!-- Navigation Arrows -->
            <button class="lightbox-nav lightbox-prev" id="btnPrevPhoto" title="Previous (Left Arrow)">
                <i class="bi bi-chevron-left fs-1"></i>
            </button>
            <button class="lightbox-nav lightbox-next" id="btnNextPhoto" title="Next (Right Arrow)">
                <i class="bi bi-chevron-right fs-1"></i>
            </button>
            
            <!-- Metadata Panel -->
            <div id="metadataPanel" class="bg-white p-4 h-100 d-none overflow-auto" style="width: 360px; z-index: 1057;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Details</h5>
                    <button type="button" class="btn-close" id="btnCloseMetadata"></button>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Filename</label>
                    <span id="metaFilename" class="text-break"></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Created</label>
                    <span id="metaDate"></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Size</label>
                    <span id="metaSize"></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Dimensions</label>
                    <span id="metaDimensions"></span>
                </div>
                <div class="mb-3" id="metaExifContainer" style="display:none;">
                    <label class="small text-muted d-block">Camera</label>
                    <span id="metaExif" class="small"></span>
                </div>
                <div class="mb-3" id="metaLocationContainer" style="display:none;">
                    <label class="small text-muted d-block">Location</label>
                    <a href="#" id="metaLocation" target="_blank" class="small text-decoration-none"></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add to Album Modal -->
<div class="modal fade" id="addToAlbumModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark text-white border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Add to Album</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="albumListContainer" class="list-group list-group-flush bg-transparent">
                    <!-- Populated via JS -->
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal (Dropzone) -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="<?= base_url('upload') ?>" class="dropzone border-primary border-dashed rounded-3" id="photoDropzone" style="background: #f8f9fa;">
                    <div class="dz-message needsclick">
                        <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i><br>
                        <h4>Drop photos here or click to upload.</h4>
                        <span class="text-muted note needsclick">(This is just a demo dropzone. Selected files are actually uploaded.)</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload()">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dropzone JS -->
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script>
    Dropzone.autoDiscover = false;
</script>
<!-- Custom JS -->
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
