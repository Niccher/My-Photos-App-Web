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
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
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
        <div class="ms-auto d-flex align-items-center">
            <button class="btn btn-outline-primary me-2" id="btnScan">
                <i class="bi bi-arrow-repeat"></i> <span class="d-none d-md-inline">Scan</span>
            </button>
            <button class="btn btn-primary" id="btnUpload" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline">Upload</span>
            </button>
            <input type="file" id="fileInput" style="display: none;" accept="image/*" multiple>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="sidebar">
            <div class="position-sticky d-flex flex-column h-100">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="bi bi-image"></i> Photos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-search"></i> Explore
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-people"></i> Sharing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-archive"></i> Archive
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-trash"></i> Trash
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
