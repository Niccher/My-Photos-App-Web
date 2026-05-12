<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2 class="h4 mb-0 fw-bold">Analytics</h2>
    <p class="text-muted small mb-0">Detailed insights into your photo library and storage usage.</p>
</div>

<!-- 1. Top Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-cloud-check text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.65rem;">Storage Used</div>
                    <div class="h5 mb-0 fw-bold"><?= $storageUsed ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-success bg-opacity-10 p-3">
                    <i class="bi bi-images text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.65rem;">Total Items</div>
                    <div class="h5 mb-0 fw-bold"><?= number_format($totalCount) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-info bg-opacity-10 p-3">
                    <i class="bi bi-share text-info fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.65rem;">Public Links</div>
                    <div class="h5 mb-0 fw-bold"><?= number_format($sharingStats['public']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-people text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.65rem;">Shared Folders</div>
                    <div class="h5 mb-0 fw-bold"><?= number_format($sharingStats['internal']) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 2. Quota Usage -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0 fw-bold text-dark">Storage Distribution</h6>
                <span class="badge bg-<?= ($storagePercent > 90) ? 'danger' : (($storagePercent > 70) ? 'warning' : 'primary') ?> bg-opacity-10 text-<?= ($storagePercent > 90) ? 'danger' : (($storagePercent > 70) ? 'warning' : 'primary') ?> rounded-pill px-3">
                    <?= round($storagePercent, 1) ?>% Full
                </span>
            </div>
            
            <div class="mb-4">
                <div class="progress rounded-pill mb-2" style="height: 10px;">
                    <div class="progress-bar rounded-pill <?= ($storagePercent > 90) ? 'bg-danger' : (($storagePercent > 70) ? 'bg-warning' : 'bg-primary') ?>" 
                         role="progressbar" 
                         style="width: <?= $storagePercent ?>%" 
                         aria-valuenow="<?= $storagePercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                    <span>Using <?= $storageUsed ?></span>
                    <span>1.00 GB Total Limit</span>
                </div>
            </div>

            <h6 class="small fw-bold text-uppercase text-muted mb-3" style="letter-spacing: 0.5px;">Recent Sharing Activity</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <tbody class="small">
                        <tr>
                            <td class="ps-0 border-0 text-muted">Public Link Shares</td>
                            <td class="pe-0 border-0 text-end fw-bold text-dark"><?= $sharingStats['public'] ?></td>
                        </tr>
                        <tr>
                            <td class="ps-0 border-0 text-muted">Internal Collaborators</td>
                            <td class="pe-0 border-0 text-end fw-bold text-dark"><?= $sharingStats['internal'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Content Distribution -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
            <h6 class="mb-4 fw-bold text-dark">File Types</h6>
            <div class="list-group list-group-flush">
                <?php foreach ($mimeStats as $stat): ?>
                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-0 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 me-3">
                            <i class="bi bi-file-earmark-<?= strpos($stat['mime_type'], 'video') !== false ? 'play' : 'image' ?> text-primary"></i>
                        </div>
                        <span class="fw-medium text-dark small"><?= strtoupper(explode('/', $stat['mime_type'])[1] ?? $stat['mime_type']) ?></span>
                    </div>
                    <span class="badge bg-light text-dark rounded-pill fw-normal"><?= number_format($stat['count']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- 4. Timeline Chart -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0 fw-bold text-dark">Upload Activity (<?= date('Y') ?>)</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-pill px-3 dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                        Annual View
                    </button>
                </div>
            </div>
            <div style="height: 350px;">
                <canvas id="uploadChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadCtx = document.getElementById('uploadChart').getContext('2d');
    const uploadLabels = <?= json_encode(array_column($monthlyQuery, 'month')) ?>;
    const uploadData = <?= json_encode(array_column($monthlyQuery, 'count')) ?>;

    // Gradient Background
    const gradient = uploadCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(66, 133, 244, 0.2)');
    gradient.addColorStop(1, 'rgba(66, 133, 244, 0.0)');

    new Chart(uploadCtx, {
        type: 'line',
        data: {
            labels: uploadLabels.length ? uploadLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Items Uploaded',
                data: uploadData.length ? uploadData : [0, 0, 0, 0, 0, 0],
                borderColor: '#4285f4',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#4285f4',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#1a1b1e',
                    bodyColor: '#5f6368',
                    borderColor: '#e0e0e0',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    titleFont: { size: 14, weight: 'bold' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f3f4', drawBorder: false },
                    ticks: { color: '#bdc1c6', padding: 10 }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#bdc1c6', padding: 10 }
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
