<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2 class="h4 mb-0">Sharing</h2>
    <p class="text-muted small mb-0">Photos and albums you've shared with others</p>
</div>

<div class="text-center py-5">
    <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
    <h3 class="mt-3 text-muted">Nothing shared yet</h3>
    <p class="text-muted">When you share photos, they will appear here.</p>
</div>

<?= $this->endSection() ?>
