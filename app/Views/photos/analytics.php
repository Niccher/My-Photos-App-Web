<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2 class="h4 mb-0">Analytics</h2>
    <p class="text-muted small mb-0">Insights into your photo library and storage usage</p>
</div>

<div class="row g-4 mb-5">
    <!-- 1. Top Summary Row -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-25 p-3">
                    <i class="bi bi-hdd-fill text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <div class="small text-white fw-bold">Total Storage</div>
                    <div class="h5 mb-0 fw-bold"><?= $storageUsed ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-25 p-3">
                    <i class="bi bi-images text-success" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <div class="small text-white fw-bold">Total Items</div>
                    <div class="h5 mb-0 fw-bold"><?= number_format($totalCount) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-25 p-3">
                    <i class="bi bi-link-45deg text-info" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <div class="small text-white fw-bold">Public Shares</div>
                    <div class="h5 mb-0 fw-bold"><?= number_format($sharingStats['public']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-25 p-3">
                    <i class="bi bi-people-fill text-warning" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <div class="small text-white fw-bold">Internal Shares</div>
                    <div class="h5 mb-0 fw-bold"><?= number_format($sharingStats['internal']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Quota & Distribution Row -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0 fw-bold">Quota Usage</h6>
                <span class="small text-white fw-bold"><?= round($storagePercent, 1) ?>% Used</span>
            </div>
            <div class="progress mb-4" style="height: 16px; background: rgba(255,255,255,0.05);">
                <div class="progress-bar progress-bar-striped progress-bar-animated rounded-pill <?= ($storagePercent > 90) ? 'bg-danger' : (($storagePercent > 70) ? 'bg-warning' : 'bg-primary') ?>" 
                     role="progressbar" 
                     style="width: <?= $storagePercent ?>%" 
                     aria-valuenow="<?= $storagePercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted">
                <span>Used: <?= $storageUsed ?></span>
                <span>Limit: 1.00 GB</span>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            
            <h6 class="mb-3 fw-bold">Sharing Activity</h6>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="border-0 py-2 small">Public Link Shares</td>
                            <td class="border-0 py-2 small text-end fw-bold"><?= $sharingStats['public'] ?></td>
                        </tr>
                        <tr>
                            <td class="border-0 py-2 small">Internal Collaborators</td>
                            <td class="border-0 py-2 small text-end fw-bold"><?= $sharingStats['internal'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm bg-dark text-white p-4">
            <h6 class="mb-3 fw-bold">Content Distribution</h6>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="border-0 text-muted small fw-normal">PHILE TYPE</th>
                            <th class="border-0 text-muted small fw-normal text-end">ITEMS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mimeStats as $stat): ?>
                        <tr>
                            <td class="border-0 py-3">
                                <i class="bi bi-file-earmark-<?= strpos($stat['mime_type'], 'video') !== false ? 'play' : 'image' ?> me-2 text-primary"></i>
                                <?= strtoupper(explode('/', $stat['mime_type'])[1] ?? $stat['mime_type']) ?>
                            </td>
                            <td class="border-0 py-3 text-end fw-bold h6 mb-0"><?= $stat['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Upload Timeline (Last Part) -->
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-dark text-white p-4">
            <h6 class="mb-4 fw-bold">Upload Timeline (<?= date('Y') ?>)</h6>
            <div style="height: 300px;">
                <canvas id="uploadChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Upload Timeline Chart
    const uploadCtx = document.getElementById('uploadChart').getContext('2d');
    const uploadLabels = <?= json_encode(array_column($monthlyQuery, 'month')) ?>;
    const uploadData = <?= json_encode(array_column($monthlyQuery, 'count')) ?>;

    new Chart(uploadCtx, {
        type: 'line',
        data: {
            labels: uploadLabels.length ? uploadLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Items Uploaded',
                data: uploadData.length ? uploadData : [0, 0, 0, 0, 0, 0],
                borderColor: '#4285f4',
                backgroundColor: 'rgba(66, 133, 244, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#4285f4'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: 'rgba(255,255,255,0.5)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: 'rgba(255,255,255,0.5)' }
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
